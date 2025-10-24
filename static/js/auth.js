// Aguarda o conteúdo da página carregar completamente
document.addEventListener('DOMContentLoaded', function() {

    // Define a URL base para o seu backend PHP. Facilita futuras alterações.
    const phpBaseUrl = 'http://localhost/tcc-projeto-final';

    // =================================================================
    // === LÓGICAS PARA A ÁREA DO USUÁRIO COMUM ===
    // =================================================================

    // --- LÓGICA PARA O FORMULÁRIO DE CADASTRO DE USUÁRIO ---
    const formCadastroUsuario = document.getElementById('formCadastroUsuario');
    if (formCadastroUsuario) {
        formCadastroUsuario.addEventListener('submit', function(event) {
            event.preventDefault();
            const messageDiv = document.getElementById('formMessage');
            messageDiv.textContent = 'Enviando...';
            messageDiv.style.color = 'gray';
            const formData = new FormData(formCadastroUsuario);
            const data = Object.fromEntries(formData.entries());

            fetch(`${phpBaseUrl}/verifica_cadastro.php`, { // URL MODIFICADA
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    messageDiv.textContent = result.message;
                    messageDiv.style.color = 'green';
                    formCadastroUsuario.reset();
                } else {
                    messageDiv.textContent = result.message;
                    messageDiv.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                messageDiv.textContent = 'Erro de conexão. Tente novamente.';
                messageDiv.style.color = 'red';
            });
        });
    }

    // --- LÓGICA PARA O FORMULÁRIO DE LOGIN DO USUÁRIO (PÁGINA INICIAL) ---
const formLoginUsuario = document.getElementById('formLoginUsuario');
if (formLoginUsuario) {
    formLoginUsuario.addEventListener('submit', function(event) {
        event.preventDefault();
        const messageDiv = document.getElementById('loginMessage');
        messageDiv.textContent = 'Verificando...';
        messageDiv.style.color = 'gray';
        const formData = new FormData(formLoginUsuario);
        const data = Object.fromEntries(formData.entries());

        // Passo 1: Fazer login no PHP
        fetch(`${phpBaseUrl}/processa_login.php`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) { throw new Error(`Erro no PHP: ${response.statusText}`); }
            return response.json();
        })
        .then(result => {
            // Verifica se o login no PHP foi bem-sucedido e retornou os dados do usuário
            if (result.success && result.userData) {
                
                localStorage.setItem('userData', JSON.stringify(result.userData));

                // Passo 2: SUCESSO NO PHP! Agora vamos avisar o Flask.
                messageDiv.textContent = 'Autenticado! Sincronizando sessão...';

                return fetch('/api/registrar-login-session', { // Chamada para a rota do Flask
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        usuario_id: result.userData.id,
                        usuario_nome: result.userData.nome 
                    })
                });
            } else {
                // Se o login no PHP falhou, joga um erro para ser pego pelo .catch()
                throw new Error(result.message || 'Usuário ou senha inválidos.');
            }
        })
        .then(flaskResponse => {
            if (!flaskResponse.ok) { throw new Error(`Erro no Flask: ${flaskResponse.statusText}`); }
            return flaskResponse.json();
        })
        .then(flaskResult => {
            // Passo 3: Verifica se o Flask confirmou o registro da sessão
            if (flaskResult.success) {
                // SUCESSO NO FLASK! Agora sim podemos redirecionar.
                messageDiv.textContent = 'Login bem-sucedido! Redirecionando...';
                messageDiv.style.color = 'green';
                setTimeout(() => {
                    window.location.href = '/painel-usuario';
                }, 1000);
            } else {
                // Se o Flask retornar um erro na sua lógica interna
                throw new Error(flaskResult.message || 'Erro ao sincronizar sessão.');
            }
        })
        .catch(error => {
            // Este .catch() pega erros de qualquer uma das etapas anteriores
            console.error('Erro no processo de login:', error);
            messageDiv.textContent = error.message;
            messageDiv.style.color = 'red';
        });
    });
}

    // =================================================================
    // === LÓGICAS PARA A ÁREA DO PSICÓLOGO ===
    // =================================================================

    // --- LÓGICA PARA O FORMULÁRIO DE CADASTRO DE PSICÓLOGO ---
    const formCadastroPsicologo = document.getElementById('formCadastroPsicologo');
    if (formCadastroPsicologo) {
        formCadastroPsicologo.addEventListener('submit', function(event) {
            event.preventDefault();
            const messageDiv = document.getElementById('formMessage');
            messageDiv.textContent = 'Enviando...';
            messageDiv.style.color = 'gray';
            const formData = new FormData(formCadastroPsicologo);
            const data = Object.fromEntries(formData.entries());

            fetch(`${phpBaseUrl}/psicologos/verifica_cadastro_psicologo.php`, { // URL MODIFICADA
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    messageDiv.textContent = result.message;
                    messageDiv.style.color = 'green';
                    formCadastroPsicologo.reset();
                } else {
                    messageDiv.textContent = result.message;
                    messageDiv.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                messageDiv.textContent = 'Erro de conexão. Tente novamente.';
                messageDiv.style.color = 'red';
            });
        });
    }

    // --- LÓGICA PARA O FORMULÁRIO DE LOGIN DO PSICÓLOGO ---
    const formLoginPsicologo = document.getElementById('formLoginPsicologo');
    if (formLoginPsicologo) {
        formLoginPsicologo.addEventListener('submit', function(event) {
            event.preventDefault();
            const messageDiv = document.getElementById('formMessage');
            messageDiv.textContent = 'Verificando...';
            messageDiv.style.color = 'gray';
            const formData = new FormData(formLoginPsicologo);
            const data = Object.fromEntries(formData.entries());

            fetch(`${phpBaseUrl}/psicologos/processa_login_psicologo.php`, { // URL MODIFICADA
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    messageDiv.textContent = 'Login bem-sucedido! Redirecionando...';
                    messageDiv.style.color = 'green';
                    if (result.psicologoData) {
                        localStorage.setItem('psicologoData', JSON.stringify(result.psicologoData));
                    }
                    setTimeout(() => {
                        window.location.href = '/painel-psicologo';
                    }, 1000);
                } else {
                    messageDiv.textContent = result.message;
                    messageDiv.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                messageDiv.textContent = 'Erro de conexão. Tente novamente.';
                messageDiv.style.color = 'red';
            });
        });
    }

    // --- LÓGICA PARA O BOTÃO DE LOGOUT DO PSICÓLOGO ---
    const logoutButton = document.getElementById('logoutButton');
    if (logoutButton) {
        logoutButton.addEventListener('click', function(event) {
            event.preventDefault();
            fetch(`${phpBaseUrl}/psicologos/logout_psicologo.php`, { // URL MODIFICADA
                method: 'POST',
                credentials: 'include'
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    localStorage.removeItem('psicologoData');
                    window.location.href = '/'; 
                } else {
                    alert('Erro ao tentar fazer logout.');
                }
            })
            .catch(error => {
                console.error('Erro na requisição de logout:', error);
                alert('Erro de conexão ao tentar fazer logout.');
            });
        });
    }

    // --- LÓGICA PARA O FORMULÁRIO DE ATUALIZAÇÃO DO PSICÓLOGO ---
    const formUpdatePsicologo = document.getElementById('formUpdatePsicologo');
    if (formUpdatePsicologo) {
        formUpdatePsicologo.addEventListener('submit', function(event) {
            event.preventDefault();
            const messageDiv = document.getElementById('formUpdateMessage');
            messageDiv.textContent = 'Salvando...';
            messageDiv.style.color = 'gray';
            const formData = new FormData(formUpdatePsicologo);
            const emailInput = document.getElementById('email');
            if (emailInput) {
                formData.append('email', emailInput.value);
            }
            const data = Object.fromEntries(formData.entries());

            fetch(`${phpBaseUrl}/psicologos/atualiza_perfil_psicologo.php`, { // URL MODIFICADA
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    messageDiv.textContent = result.message;
                    messageDiv.style.color = 'green';
                    if (result.updatedData) {
                        const oldData = JSON.parse(localStorage.getItem('psicologoData'));
                        const newData = { ...oldData, ...result.updatedData };
                        localStorage.setItem('psicologoData', JSON.stringify(newData));
                        document.getElementById('nome-psicologo-titulo').textContent = newData.nome.split(' ')[0];
                    }
                } else {
                    messageDiv.textContent = result.message;
                    messageDiv.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                messageDiv.textContent = 'Erro de conexão. Tente novamente.';
                messageDiv.style.color = 'red';
            });
        });
    }
});