<link href="https://fonts.googleapis.com/css2?family=Montserra:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/header/header.css') }}">

<header class="header">
    <div class="header-content">
        {% if app.request.get('_route') != 'app_bienvenue' %}
            <button class="menu-btn" onclick="toggleSidebar()">☰</button>
        {% else %}
            <div></div>
        {% endif %}
        <div class="titre-logo">
            <img src="{{ asset('images/header/logo-site.png') }}" alt="Logo Site Web" id="logo">
            <h1>SBR Temp'</h1>
        </div>
        <div class="icons">
            <!-- Bouton pour signaler une anomalie -->
            <div class="problem-report">
                <button onclick="window.location.href='{{ path('app_anomalie', {role}) }}'" class="report-button">Signaler</button>
            </div>
            {% if role == 'Manager' or role == 'Technicien' %}
            <div class="notification">

                <a class="icon" href="{{ path('app_notification', {role}) }}">🔔</a>
                {% if utilisateur.nbNotif > 0 %}
                    <span class="badge">{{ utilisateur.nbNotif }}</span>
                {% endif %}
            </div>

            <div class="dropdown">
                <div class="profile">
                    <button class="mainmenubtn">👤</button>
                    <p id="name-display">{{ nomUtilisateur }}</p> <!-- Affichage du rôle -->
                </div>
                <div class="dropdown-child">
                    {% if app.request.get('_route') != 'app_profil' and app.request.get('_route') != 'app_gestion_salles' and app.request.get('_route') != 'app_gestion_salle_ajouter' and app.request.get('_route') != 'app_gestion_salle_modifier' and app.request.get('_route') != 'app_gestion_salle_supprimer_confirm' and app.request.get('_route') != 'app_gestion_salle_supprimer' and app.request.get('_route') != 'app_anomalie'%}
                        {% if utilisateur.fonction == 'Manager' %}
                        <select id="role-select" onchange="redirectToRolePage()">
                            <option value="Manager" {% if role == 'Manager' %}selected{% endif %}>Manager</option>
                            <option value="Technicien" {% if role == 'Technicien' %}selected{% endif %}>Technicien</option>
                        </select>
                        {% endif %}
                    {% endif %}
                    <a href="{{ path('app_profil', {role}) }}">Profil </a>
                    <a href="{{ path('app_logout') }}" class="logout-btn">Se déconnecter</a>
                </div>
            </div>
        </div>
        {% endif %}
    </div>
    <!-- Champ caché pour la page actuelle -->
    <input type="hidden" id="current-page" value="{{ app.request.uri }}">
</header>

<script>
    // Fonction de redirection après sélection du rôle
    function redirectToRolePage() {
        const roleSelect = document.getElementById("role-select");
        const selectedRole = roleSelect.value; // Récupérer le rôle sélectionné
        const currentPage = document.getElementById("current-page").value; // URL actuelle

        if (!selectedRole) {
            alert("Veuillez sélectionner un rôle valide !");
            return;
        }

        // Stocker le rôle dans localStorage
        localStorage.setItem('selectedRole', selectedRole);

        // Extraire la salle si elle est présente dans l'URL
        let salle = '';
        const salleMatch = currentPage.match(/donnees\/([^?\/]+)/);
        if (salleMatch) {
            salle = salleMatch[1];
        }

        // Construire la nouvelle URL en incluant le rôle et la salle
        let redirectUrl = '';
        if (currentPage.includes('accueil')) {
            redirectUrl = `/${selectedRole}/accueil`;
        } else if (currentPage.includes('donnees')) {
            redirectUrl = `/${selectedRole}/donnees/${salle}`;
        } else if (currentPage.includes('gestion-sa')) {
            if (currentPage.includes('gestion-sa/ajouter')){
                redirectUrl = `/${selectedRole}/gestion-sa/ajouter`;
            } else {
                redirectUrl = `/${selectedRole}/gestion-sa`;
            }
        } else if (currentPage.includes('bienvenue'))
        {
            redirectUrl = `/${selectedRole}/bienvenue`;
        } else if (currentPage.includes('/choix-salles'))
        {
            redirectUrl = `/${selectedRole}/choix-salles`;
        } else if (currentPage.includes('notification'))
        {
            redirectUrl = `/${selectedRole}/notification`;
        }

        window.location.href = redirectUrl;
    }

    // Charger le rôle sélectionné depuis l'URL et mettre à jour l'affichage
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const pathParts = window.location.pathname.split('/');
        const roleFromUrl = pathParts[1]; // Le rôle est dans la première partie de l'URL

        // Mettre à jour le sélecteur et l'affichage du rôle en fonction de l'URL
        const roleSelect = document.getElementById("role-select");
        const roleDisplay = document.getElementById("role-display");

        if (roleFromUrl) {
            roleSelect.value = roleFromUrl;
        }

        // Charger le rôle stocké dans localStorage si disponible et le mettre à jour
        const storedRole = localStorage.getItem('selectedRole');
        if (storedRole && !roleFromUrl) { // Si le rôle n'est pas dans l'URL, on charge celui du localStorage
            roleSelect.value = storedRole;
            roleDisplay.textContent = storedRole;
        }
    });

    // Fonction pour afficher/masquer la barre latérale
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        sidebar.classList.toggle("active");
    }

    // Fermer la sidebar lorsqu'on clique en dehors
    document.addEventListener("click", (event) => {
        const sidebar = document.getElementById("sidebar");
        const menuButton = document.querySelector(".menu-btn");
        if (!sidebar.contains(event.target) && !menuButton.contains(event.target) && sidebar.classList.contains("active")) {
            toggleSidebar(); // Fermer la sidebar si elle est active et qu'on clique en dehors
        }
    });

</script>