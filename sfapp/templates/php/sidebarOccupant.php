<link rel="stylesheet" href="{{ asset('css/sidebar/sidebar.css') }}">

<!-- Volet déroulant -->
<div class="sidebar" id="sidebar">
    <button class="close-btn" onclick="toggleSidebar()">✖</button>

    <aside class="side-menu">
        <div class="menu-item-title">
            <!-- Formulaire pour sélectionner la salle -->
            <form method="GET" action="{{ path('app_donnees', { role: role, salle: salleNom }) }}">
                <label for="salle-select">Sélectionnez une salle</label>
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


        <div class="menu-item {% if app.request.get('_route') == 'app_accueil' %}active{% endif %}">
            <img src="{{ asset('images/donnees/accueil.png') }}" alt="Accueil" class="menu-icon">
            <a href="{{ path('app_accueil', { role: role }) }}">Accueil</a>
        </div>
        <div class="menu-item {% if app.request.get('_route') == 'app_choix_salles' %}active{% endif %}">
            <img src="{{ asset('images/donnees/choix_salles.png') }}" alt="ChoixSalles" class="menu-icon">
            <a href="{{ path('app_choix_salles', { role: role }) }}">Choix des salles</a>
        </div>
        <div class="menu-item {% if app.request.get('_route') == 'app_donnees' %}active{% endif %}">
            <img src="{{ asset('images/donnees/historique.png') }}" alt="Donnees" class="menu-icon">
            <a href="{{ path('app_donnees', { role: role, salle: salleNom }) }}">Données climatiques</a>
        </div>
    </aside>
</div>