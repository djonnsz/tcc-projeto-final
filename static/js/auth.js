// Aguarda o conteúdo da página carregar completamente
document.addEventListener('DOMContentLoaded', function() {

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

            fetch('/api/verifica_cadastro.php', { // URL CORRIGIDA
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

            fetch('/api/processa_login.php', {  // URL CORRIGIDA
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
                    if (result.userData) {
                        localStorage.setItem('userData', JSON.stringify(result.userData));
                    }
                    setTimeout(() => {
                        window.location.href = '/painel-usuario';
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

    // --- LÓGICA PARA O BOTÃO DE LOGOUT DO USUÁRIO ---
    const logoutUsuarioButton = document.getElementById('logoutUsuarioButton');
    if (logoutUsuarioButton) {
        logoutUsuarioButton.addEventListener('click', function(event) {
            event.preventDefault();
            localStorage.removeItem('userData');
            // Futuramente, podemos adicionar uma chamada a um logout.php aqui
            window.location.href = '/'; 
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

            fetch('/api/psicologos/verifica_cadastro_psicologo.php', { // URL CORRIGIDA
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

            fetch('/api/psicologos/processa_login_psicologo.php', { // URL CORRIGIDA
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
            fetch('/api/psicologos/logout_psicologo.php', { // URL CORRIGIDA
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

            fetch('/api/psicologos/atualiza_perfil_psicologo.php', { // URL CORRIGIDA
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