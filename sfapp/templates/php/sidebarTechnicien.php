<link rel="stylesheet" href="{{ asset('css/sidebar/sidebar.css') }}">

<!-- Volet déroulant -->
<div class="sidebar" id="sidebar">
    <button class="close-btn" onclick="toggleSidebar()">✖</button>

    <aside class="side-menu">
        <div class="menu-item-title">
            <!-- Formulaire pour sélectionner la salle -->
            <form method="GET" action="{{ path('app_donnees', { role: role, salle: salleNom }) }}">
                <label for="salle-select">Sélectionnez une salle :</label>
                <select name="salleNom" id="salle-select" onchange="this.form.submit()">
                    {% for salle in salles %}
                    {%  if salle.SA is not null %}
                    <option value="{{ salle.nom }}" {% if salle.nom== salleNom %}selected{% endif %}>
                        {{ salle.nom }}
                    </option>
                    {% endif %}
                    {% endfor %}
                </select>
            </form>
        </div>

        <div class="menu-item {% if app.request.get('_route') == 'app_choix_salles' %}active{% endif %}">
            <img src="{{ asset('images/donnees/choix_salles.png') }}" alt="ChoixSalles" class="menu-icon">
            <a href="{{ path('app_choix_salles', { role: role }) }}">Choix des salles</a>
        </div>
        <div class="menu-item {% if app.request.get('_route') == 'app_donnees' %}active{% endif %}">
            <img src="{{ asset('images/donnees/historique.png') }}" alt="Données climattiques" class="menu-icon">
            <a href="{{ path('app_donnees', { role: role, salle: salleNom }) }}">Données climatiques</a>
        </div>
        <div class="menu-item {% if app.request.get('_route') == 'app_gestion_sa' %}active{% endif %}">
            <img src="{{ asset('images/donnees/sa.png') }}" alt="Systèmes" class="menu-icon">
            <a href="{{ path('app_gestion_sa', { role: role }) }}">Systèmes d'acquisition</a>
        </div>
        <div class="menu-item {% if app.request.get('_route') == 'app_gestion_salles' %}active{% endif %}">
            <img src="{{ asset('images/donnees/gestion.png') }}" alt="Gestion" class="menu-icon">
            <a href="{{ path('app_gestion_salles', { role: role }) }}">Gestion des salles</a>
        </div>
        <div class="menu-item {% if app.request.get('_route') == 'app_parametres' %}active{% endif %}">
            <img src="{{ asset('images/donnees/parametre.png') }}" alt="Paramètres" class="menu-icon">
            <a href="{{ path('app_parametres', { role: role }) }}">Paramètres des seuils</a>
        </div>
    </aside>
</div>