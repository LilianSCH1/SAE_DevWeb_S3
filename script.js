// Variables pour stocker les données chargées depuis la base de données
let musicsData = [];
let artistsData = [];
let groupsData = [];

// Effet de défilement sur la barre de navigation
const navbar = document.querySelector(".navbar");
const navbarBrand = document.querySelector(".navbar-brand");
const navbarLogo = document.querySelector(".navbar-brand img");
window.addEventListener("scroll", () => {
    if (window.scrollY > 50) {
        navbar.classList.add("scrolled");
        navbarBrand.classList.add("scrolled");
        if (navbarLogo) {
            navbarLogo.src = "icons/logos/MyPulse_White-removebg-preview.png";
        }
    } else {
        navbar.classList.remove("scrolled");
        navbarBrand.classList.remove("scrolled");
        if (navbarLogo) {
            navbarLogo.src = "icons/logos/MyPulse-removebg-preview.png";
        }
    }
});

// Animation des compteurs statistiques
const counters = document.querySelectorAll(".counter");
function animateCounters() {
    counters.forEach((counter) => {
        const target = +counter.getAttribute("data-target");
        const count = +counter.innerText;
        const increment = target / 200;
        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(animateCounters, 1);
        } else {
            counter.innerText = target;
        }
    });
}

// Observer pour déclencher l'animation des compteurs
const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting) {
            animateCounters();
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

if (document.querySelector(".stats")) {
    observer.observe(document.querySelector(".stats"));
}

// Fonction pour créer une carte de musique
function createMusicCard(music) {
    return `
        <div class="col-md-6 col-lg-4">
            <div class="music-card">
                <img src="${music.cover}" alt="${music.title}" class="music-cover">
                <h5 class="music-title">${music.title}</h5>
                <p class="music-artist">${music.artist}</p>
                <div class="categories">
                    ${music.categories.map(cat => `<span class="category-badge">${cat}</span>`).join('')}
                </div>
                <audio controls class="audio-player">
                    <source src="${music.audio}" type="audio/mpeg">
                </audio>
                <div class="vote-section">
                    <div>
                        <div class="vote-counter">${music.votes}</div>
                        <div class="vote-label">vote${music.votes > 1 ? 's' : ''}</div>
                    </div>
                    <button class="btn btn-vote btn-sm" onclick="vote(this)">
                        <i class="bi bi-hand-thumbs-up"></i> Voter
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Fonction pour créer une carte d'artiste
function createArtistCard(artist) {
    return `
        <div class="col-md-6 col-lg-4">
            <div class="music-card">
                <img src="${artist.image}" alt="${artist.name}" class="music-cover">
                <h5 class="music-title">${artist.name}</h5>
                <p class="music-artist">${artist.role}</p>
                <div class="categories">
                    ${artist.categories.map(cat => `<span class="category-badge">${cat}</span>`).join('')}
                </div>
                <audio controls class="audio-player">
                    <source src="${artist.audio}" type="audio/mpeg">
                </audio>
                <div class="vote-section">
                    <div>
                        <div class="vote-counter">${artist.votes}</div>
                        <div class="vote-label">vote${artist.votes > 1 ? 's' : ''}</div>
                    </div>
                    <button class="btn btn-vote btn-sm" onclick="vote(this)">
                        <i class="bi bi-hand-thumbs-up"></i> Voter
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Fonction pour créer une carte de groupe
function createGroupCard(group) {
    return `
        <div class="col-md-6 col-lg-4">
            <div class="music-card">
                <img src="${group.image}" alt="${group.name}" class="music-cover">
                <h5 class="music-title">${group.name}</h5>
                <p class="music-artist">Fondé en ${group.founded}</p>
                <div class="categories">
                    ${group.categories.map(cat => `<span class="category-badge">${cat}</span>`).join('')}
                </div>
                <audio controls class="audio-player">
                    <source src="${group.audio}" type="audio/mpeg">
                </audio>
                <div class="vote-section">
                    <div>
                        <div class="vote-counter">${group.votes}</div>
                        <div class="vote-label">vote${group.votes > 1 ? 's' : ''}</div>
                    </div>
                    <button class="btn btn-vote btn-sm" onclick="vote(this)">
                        <i class="bi bi-hand-thumbs-up"></i> Voter
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Initialisation des grilles (sera remplacé par les données de la BDD)
if (document.getElementById("musics-grid")) {
    document.getElementById("musics-grid").innerHTML = musicsData.map(createMusicCard).join('');
}
if (document.getElementById("artists-grid")) {
    document.getElementById("artists-grid").innerHTML = artistsData.map(createArtistCard).join('');
}
if (document.getElementById("groups-grid")) {
    document.getElementById("groups-grid").innerHTML = groupsData.map(createGroupCard).join('');
}

// Fonction de vote
function vote(button) {
    if (button.classList.contains('voted')) {
        alert('Vous avez déjà voté pour ce contenu');
        return;
    }
    const counter = button.parentElement.querySelector('.vote-counter');
    let votes = parseInt(counter.innerText);
    votes++;
    counter.innerText = votes;
    button.classList.add('voted');
    button.innerText = '✓ Voté';
    button.disabled = true;
}

// Fonction pour afficher une section
function showSection(sectionId) {
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(sectionId).classList.add('active');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Initialisation avec la section d'accueil
if (document.getElementById('home')) {
    document.getElementById('home').classList.add('active');
}
