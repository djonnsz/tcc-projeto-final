from flask import Flask, render_template, request, jsonify
import os
import uuid
import re
import unicodedata
from pathlib import Path
from dotenv import load_dotenv
from openai import OpenAI  # SDK novo 1.x

# ============================ CONFIG ============================ #
# Carrega .env ao lado do app.py (robusto mesmo se o CWD variar)
ENV_PATH = Path(__file__).parent / ".env"
load_dotenv(dotenv_path=ENV_PATH, override=True)

OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")
if not OPENAI_API_KEY:
    raise RuntimeError(f"OPENAI_API_KEY não encontrada. Coloque no .env em {ENV_PATH}")

# Cliente OpenAI (SDK 1.x)
client = OpenAI(api_key=OPENAI_API_KEY)

app = Flask(__name__)

# ============================ SYSTEM PROMPT ============================ #
SYSTEM_PROMPT = (
    "Você é um amigo próximo e um cachorro virtual, empático e confiável. "
    "Apoie pessoas afetadas por vício em apostas (usuários e familiares) com respostas curtas e úteis.\n\n"

    "=== ESTILO ===\n"
    "- Frases curtas. Uma ideia por linha. Sem parágrafos longos.\n"
    "- Varie o vocabulário: evite repetir “como você se sente?”.\n"
    "- Mostre presença e incentivo, sem julgamentos.\n"
    "- Nunca indique links/serviços/profissionais específicos. Não normalize apostas.\n\n"

    "=== FOCO EM AÇÃO (evitar interrogatório) ===\n"
    "- Priorize 1–2 sugestões práticas por resposta (micro-passos).\n"
    "- Máximo 1 pergunta por resposta, e NÃO faça perguntas em respostas consecutivas.\n"
    "- Se fizer pergunta, que seja aberta e diferente (ex.: ‘O que te ajudaria agora?’ ‘Qual pequeno passo cabe hoje?’).\n"
    "- Quando possível, ofereça escolhas: ‘Preferes tentar X ou Y?’\n\n"

    "=== SUGESTÕES PRÁTICAS (exemplos) ===\n"
    "- Adiar a vontade: esperar 10 minutos e respirar fundo 4x.\n"
    "- Trocar o impulso: sair para uma volta, tomar água, música curta, banho rápido.\n"
    "- Dificultar acesso: evitar app/site à noite, guardar cartão/dinheiro longe.\n"
    "- Anotar gatilhos em 3 palavras. Celebrar cada pequeno ‘não apostei’.\n"
    "- Preencher com estudo/hobby: 15 min de leitura, aula curta, vídeo de aprendizado.\n"
    "- Para familiares: separar pessoa do comportamento, limites claros, autocuidado.\n\n"

    "=== EXEMPLOS REAIS (genéricos) ===\n"
    "- ‘Muita gente relata que começou a melhorar quando trocou o momento de aposta por caminhada curta.’\n"
    "- ‘Outros foram anotando gatilhos e perceberam que adiar 10 minutos reduzia a vontade.’\n"
    "- ‘Teve quem focou em estudar algo novo e sentiu orgulho das pequenas vitórias.’\n\n"

    "=== FORMATO DA RESPOSTA ===\n"
    "- 3 a 5 linhas curtas.\n"
    "- Misture: 1 validação breve + 1–2 dicas práticas + (opcional) 1 pergunta NÃO repetitiva.\n"
    "- Se o usuário já explicou bastante, NÃO pergunte de novo: apenas ofereça 1–2 próximos passos.\n\n"

    "=== OBJETIVO ===\n"
    "Acolher, reduzir repetição de perguntas, e orientar com passos pequenos e variados para se afastar das apostas."
)



# ============================ ESTADO ============================ #
usuarios_humano = set()      # sessões que pediram profissional (ou auto-encaminhadas)
# sessions: { session_id: [ {id:"uuid", sender:"user"/"bot"/"human", text:"..."} ] }
sessions = {}

# ============================ DETECÇÃO DE CRITICIDADE ============================ #
def _normalize_base(s: str) -> str:
    s = s or ""
    s = s.lower()
    s = unicodedata.normalize("NFD", s)
    s = "".join(ch for ch in s if unicodedata.category(ch) != "Mn")  # remove acentos
    s = re.sub(r"[’'`´]", "'", s)
    s = re.sub(r"[^\w\s'!?💔😭😢😞]", " ", s, flags=re.UNICODE)
    s = re.sub(r"\s+", " ", s).strip()
    return s

def _leet_to_plain(s: str) -> str:
    return (s.replace("0", "o")
             .replace("1", "i")
             .replace("3", "e")
             .replace("4", "a")
             .replace("5", "s")
             .replace("7", "t"))

def _collapse_repeats(s: str) -> str:
    return re.sub(r"(.)\1{2,}", r"\1\1", s)

def _normalize_all(s: str) -> str:
    return _collapse_repeats(_leet_to_plain(_normalize_base(s)))

def _lev_dist(a: str, b: str) -> int:
    dp = list(range(len(b) + 1))
    for i, ca in enumerate(a, 1):
        prev = i - 1
        dp[0] = i
        for j, cb in enumerate(b, 1):
            tmp = dp[j]
            dp[j] = prev if ca == cb else 1 + min(prev, dp[j], dp[j-1])
            prev = tmp
    return dp[-1]

def _fuzzy_includes(hay: str, needle: str, max_edits: int = 1) -> bool:
    if needle in hay:
        return True
    n = len(needle)
    if n == 0 or len(hay) < n:
        return False
    max_edits = min(max_edits + n // 12, 2)
    for i in range(0, len(hay) - n + 1):
        if _lev_dist(hay[i:i+n], needle) <= max_edits:
            return True
    return False

def _fuzzy_any(hay: str, variants, max_edits: int = 1) -> bool:
    return any(_fuzzy_includes(hay, _normalize_all(v), max_edits) for v in variants if v)

CRITICAL_PATTERNS = {
    "suicidio": [
        "suicidio","me matar","tirar minha vida","acabar com tudo",
        "quero morrer","sem vontade de viver","nao quero mais viver",
        "pensando em morrer","ideacao suicida","quero sumir"
    ],
    "distress": [
        "nao aguento","nao aguento mais","desespero","sem saida","sem saída",
        "to mal","tô mal","muito mal","pior dia","crise de panico","crise panico",
        "ataque de panico","ansiedade forte","desesperado","no fundo do poco","no fundo do poço",
        "sou um lixo","nao presto","nao vejo futuro","sofrendo demais"
    ],
    "help": [
        "quero ajuda","preciso de ajuda","socorro","me ajuda","ajuda por favor",
        "urgente","falar com alguem","conversar com alguem","preciso falar com alguem",
        "posso falar com alguem","quero conversar com alguem","apoio agora"
    ],
    "gambling_crisis": [
        "nao consigo parar de apostar","nao consigo parar","compulsao por apostar","compulsao por jogo",
        "recaida","recaída","voltei a apostar","perdi tudo","perdi meu salario","perdi meu salário",
        "endividado","muita divida","muitas dividas","divida com agiota","cobranca pesada",
        "quebrado","rompi limite"
    ],
    "family": [
        "meu marido aposta","minha esposa aposta","meu filho viciado","minha filha viciada",
        "meu pai viciado","minha mae viciada","meu irmão viciado","minha irmã viciada",
        "meu namorado aposta","minha namorada aposta","alguem proximo viciado","alguém próximo viciado",
        "ajuda para familia","sou familiar de viciado","sou parente de viciado"
    ]
}
CORE_SIGNALS = [
    "me matar","acabar com tudo","quero morrer","nao aguento mais",
    "preciso de ajuda","socorro","sem saida","muito mal",
    "nao consigo parar","perdi tudo","endividado"
]

def detect_critical(text: str) -> bool:
    if not text or not text.strip():
        return False
    raw = text
    n = _normalize_all(raw)
    if any(e in raw for e in ["😭", "💔", "😢", "😞"]):
        return True
    for variants in CRITICAL_PATTERNS.values():
        if _fuzzy_any(n, variants, 1):
            return True
    if _fuzzy_any(n, CORE_SIGNALS, 2):
        return True
    return False

# ============================ ROTAS DE PÁGINA ============================ #
@app.route("/")
def home():
    return render_template("index.html")

@app.route("/noticias")
def noticias():
    return render_template("noticias.html")

@app.route("/painel")
def painel():
    return render_template("painel.html")

@app.route("/contato")
def contato():
    return render_template("contato.html")


@app.get("/health")
def health():
    return "OK"

# ============================ APIS DO PAINEL ============================ #
@app.route("/lista_sessoes")
def lista_sessoes():
    return jsonify(list(usuarios_humano))

@app.route("/mensagens/<session_id>")
def mensagens(session_id):
    return jsonify(sessions.get(session_id, []))

@app.route("/enviar_profissional/<session_id>", methods=["POST"])
def enviar_profissional(session_id):
    data = request.get_json(force=True) or {}
    texto = (data.get("message") or "").strip()
    if not texto:
        return jsonify({"ok": False, "error": "Mensagem vazia"}), 400

    msg_id = str(uuid.uuid4())
    sessions.setdefault(session_id, []).append({
        "id": msg_id, "sender": "human", "text": texto
    })
    print(f"[PROFISSIONAL -> {session_id}] {texto}")
    return jsonify({"ok": True, "id": msg_id})

@app.route("/perfil/meu")
def perfil_meu():
    perfil = {
        "nome": "Dr. Gabriel",
        "especialidade": "Psicólogo Clínico",
        "biografia": "Experiência no atendimento a vícios e suporte emocional."
    }
    return jsonify(perfil)

# ============================ APIS DO USUÁRIO ============================ #
@app.route("/status_sessao/<session_id>")
def status_sessao(session_id):
    return jsonify({"humano": session_id in usuarios_humano})

@app.route("/transfer", methods=["POST"])
def transfer():
    data = request.get_json(force=True) or {}
    session_id = data.get("session_id")
    if not session_id:
        return jsonify({"status": "error", "message": "session_id não informado"}), 400

    usuarios_humano.add(session_id)
    sessions.setdefault(session_id, [])
    sessions[session_id].append({
        "id": str(uuid.uuid4()),
        "sender": "bot",
        "text": "Você será atendido por um profissional em instantes. Aguarde aqui."
    })

    print(f"[TRANSFER] Sessão {session_id} marcada para atendimento humano")
    return jsonify({"status": "ok", "message": "Pedido de atendimento humano recebido."})

@app.route("/encerrar/<session_id>", methods=["POST"])
def encerrar(session_id):
    usuarios_humano.discard(session_id)
    if session_id in sessions:
        sessions[session_id].append({
            "id": str(uuid.uuid4()),
            "sender": "bot",
            "text": "A conversa com o profissional foi encerrada. Volte quando quiser conversar novamente."
        })
    return jsonify({"ok": True})

@app.route("/send", methods=["POST"])
def send():
    data = request.get_json(force=True) or {}
    user_message = (data.get("message") or "").strip()
    session_id = data.get("session_id")

    if not session_id:
        return jsonify({"reply": "Sessão não identificada."}), 400
    if not user_message:
        return jsonify({"reply": "Mensagem vazia."}), 400

    sessions.setdefault(session_id, [])

    # registra mensagem do usuário
    user_msg_id = str(uuid.uuid4())
    sessions[session_id].append({"id": user_msg_id, "sender": "user", "text": user_message})
    print(f"[USUÁRIO {session_id}] {user_message}")

    # se já aguardando humano, IA não responde
    if session_id in usuarios_humano:
        print(f"[AGUARDANDO PROFISSIONAL] {session_id} - IA desligada")
        return jsonify({
            "reply": "Um profissional está atendendo você. Aguarde a resposta dele aqui.",
            "id": user_msg_id
        })

    # AUTO-ENCAMINHAMENTO (detecção de criticidade)
    if detect_critical(user_message):
        usuarios_humano.add(session_id)
        sessions[session_id].append({
            "id": str(uuid.uuid4()),
            "sender": "bot",
            "text": "Sinto muito que você esteja passando por isso. Vou acionar um profissional agora. Fique aqui, ele já responde."
        })
        print(f"[AUTO-HANDOFF] Sessão {session_id} marcada para atendimento humano")
        return jsonify({
            "reply": "Estou aqui com você. Acionei um profissional para continuar essa conversa com cuidado, tudo bem?",
            "handoff": True
        })

    # RESPOSTA NORMAL DA IA (SDK 1.x)
    try:
        resp = client.chat.completions.create(
            model="gpt-4o-mini",  # escolha um modelo disponível pra sua conta
            messages=[
                {"role": "system", "content": SYSTEM_PROMPT},
                {"role": "user", "content": user_message}
            ],
            temperature=0.7,
            timeout=30
        )
        bot_reply = resp.choices[0].message.content.strip()

        bot_msg_id = str(uuid.uuid4())
        sessions[session_id].append({
            "id": bot_msg_id,
            "sender": "bot",
            "text": bot_reply
        })

        print(f"[IA -> {session_id}] {bot_reply}")
        return jsonify({"reply": bot_reply, "id": bot_msg_id})

    except Exception as e:
        # Loga e devolve erro visível
        print(f"[ERRO IA] {e}")
        return jsonify({"reply": f"Erro: {str(e)}"}), 500

# ============================ MAIN ============================ #
if __name__ == "__main__":
    # Em produção use WSGI (gunicorn/uwsgi) e debug=False
    app.run(host="127.0.0.1", port=5000, debug=True)
