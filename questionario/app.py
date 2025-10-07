
from flask import Flask, render_template, request, jsonify
app = Flask(__name__)

QUESTIONS = [
    "Você sente vontade intensa de apostar mesmo quando não pode?",
    "Já mentiu para familiares ou amigos sobre quanto gastou em apostas?",
    "Perdeu dinheiro que precisava para contas ou despesas por apostar?",
    "Tenta recuperar perdas apostando mais ("chasing losses")?",
    "Já sacrificou hobbies, trabalho ou estudo por causa das apostas?",
    "Sente ansiedade, irritação ou inquietação quando tenta parar de apostar?",
    "Usa apostas como escape para problemas ou emoções negativas?",
    "Aumentou o valor das apostas com o tempo para alcançar o mesmo nível de excitação?",
    "Pediu dinheiro emprestado ou vendeu bens para apostar?",
    "Já pensou em suicídio ou teve ideias autodestrutivas por causa das apostas?"
]

# weights for answers
ANSWER_SCORES = {
    "sempre": 5,
    "quase sempre": 4,
    "às vezes": 2,
    "nunca": 0
}

def calculate_score(answers):
    # answers is a list of strings matching keys in ANSWER_SCORES
    total = 0
    for a in answers:
        total += ANSWER_SCORES.get(a.lower(), 0)
    # Normalize: map total (0..50) to 1..5 scale
    max_total = 5 * len(QUESTIONS)
    if max_total == 0:
        return 1, total
    # scaled_score between 1 and 5
    scaled = 1 + (total / max_total) * 4
    return round(scaled, 2), total

@app.route('/')
def index():
    return render_template('index.html', questions=QUESTIONS)

@app.route('/submit', methods=['POST'])
def submit():
    data = request.json
    answers = data.get('answers', [])
    scaled, raw = calculate_score(answers)
    # Interpretative text
    if scaled < 1.8:
        level = 1
        text = "Pouco risco de vício. Fique atento e jogue com responsabilidade."
    elif scaled < 2.6:
        level = 2
        text = "Risco leve. Considere monitorar seu comportamento e definir limites."
    elif scaled < 3.4:
        level = 3
        text = "Risco moderado. Procure apoio e reveja seus hábitos."
    elif scaled < 4.2:
        level = 4
        text = "Alto risco. Considere buscar ajuda profissional e limitar acesso a jogos."
    else:
        level = 5
        text = "Muito alto risco / provável vício. Procure orientação profissional imediatamente."
    return jsonify({
        "score_scaled": scaled,
        "score_raw": raw,
        "level": level,
        "message": text
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
