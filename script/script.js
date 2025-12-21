function togglePassword(fieldId) {
  const input = document.getElementById(fieldId);
  const icon = input.nextElementSibling.querySelector("i");
  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("bi-eye");
    icon.classList.add("bi-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("bi-eye-slash");
    icon.classList.add("bi-eye");
  }
}

// Scroll event listener for navbar
window.addEventListener("scroll", function () {
  const navbar = document.querySelector(".navbar");
  const logo = document.getElementById("logo");
  if (window.scrollY > 50) {
    navbar.classList.add("scrolled");
    if (logo) {
      logo.src = "../icons/logos/MyPulse_White-removebg-preview.png";
    }
  } else {
    navbar.classList.remove("scrolled");
    if (logo) {
      logo.src = "../icons/logos/MyPulse-removebg-preview.png";
    }
  }
});

document.addEventListener("DOMContentLoaded", function () {
  // Music covers auto-scroll
  const scrollContent = document.querySelector(".scroll-content");
  if (scrollContent) {
    const originalContent = scrollContent.innerHTML;
    scrollContent.innerHTML += originalContent;
  }

  // Audio player (vote page)
  const audio = document.getElementById("vote-audio-player");
  let currentBtn = null;
  let currentProgressBar,
    currentProgressFill,
    currentProgressTime,
    currentPlayPauseBtn;

  function setBtnState(btn, isPlaying) {
    const container = btn.closest(".audio-player-container");
    if (!container) return;
    const progressContainer = container.querySelector(
      ".progress-bar-container"
    );
    const playBtn = container.querySelector(".btn-play-audio");

    if (!progressContainer || !playBtn) return;

    if (isPlaying) {
      playBtn.style.display = "none";
      progressContainer.style.display = "flex";
      currentProgressBar = progressContainer.querySelector(".progress-bar-bg");
      currentProgressFill =
        progressContainer.querySelector(".progress-bar-fill");
      currentProgressTime = progressContainer.querySelector(".progress-time");
      currentPlayPauseBtn = progressContainer.querySelector(".btn-play-pause");
      if (currentPlayPauseBtn) {
        currentPlayPauseBtn.textContent = "⏸";
        currentPlayPauseBtn.classList.add(...playBtn.classList);
      }
    } else {
      playBtn.style.display = "block";
      progressContainer.style.display = "none";
    }
  }

  if (audio) {
    // Boutons "Écouter"
    document.querySelectorAll(".btn-play-audio").forEach((btn) => {
      btn.addEventListener("click", () => {
        const src = btn.getAttribute("data-audio");
        if (!src) return;

        if (currentBtn === btn && !audio.paused) {
          audio.pause();
          setBtnState(btn, false);
          currentBtn = null;
          return;
        }

        if (currentBtn && currentBtn !== btn) {
          setBtnState(currentBtn, false);
        }

        audio.src = src;
        audio
          .play()
          .then(() => {
            setBtnState(btn, true);
            currentBtn = btn;
          })
          .catch(() => {});
      });
    });

    // Bouton play/pause dans la barre de progression
    document.addEventListener("click", (e) => {
      if (
        e.target.classList.contains("btn-play-pause") ||
        e.target.closest(".btn-play-pause")
      ) {
        const btn = e.target.classList.contains("btn-play-pause")
          ? e.target
          : e.target.closest(".btn-play-pause");
        if (audio.paused) {
          audio.play();
          btn.textContent = "⏸";
        } else {
          audio.pause();
          btn.textContent = "▶";
        }
      }
    });

    // Mettre à jour la barre de progression
    audio.addEventListener("timeupdate", () => {
      if (currentProgressFill && audio.duration) {
        const percent = (audio.currentTime / audio.duration) * 100;
        currentProgressFill.style.width = percent + "%";

        if (currentProgressTime) {
          const minutes = Math.floor(audio.currentTime / 60);
          const seconds = Math.floor(audio.currentTime % 60);
          currentProgressTime.textContent = `${minutes}:${seconds
            .toString()
            .padStart(2, "0")}`;
        }
      }
    });

    // Clic sur la barre pour changer la position
    document.addEventListener("click", (e) => {
      if (e.target.classList.contains("progress-bar-bg")) {
        const rect = e.target.getBoundingClientRect();
        const percent = (e.clientX - rect.left) / rect.width;
        audio.currentTime = percent * audio.duration;
      }
    });

    // Clic en dehors du lecteur audio pour réinitialiser
    document.addEventListener("click", (e) => {
      if (currentBtn && !e.target.closest(".audio-player-container")) {
        audio.pause();
        setBtnState(currentBtn, false);
        currentBtn = null;
      }
    });
  }

  // Bouton + / - description
  document.querySelectorAll(".toggle-desc-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".content-card");
      const desc = card
        ? card.querySelector(".content-card-description")
        : null;
      if (!desc) return;

      const isShown = desc.classList.toggle("show");
      btn.textContent = isShown ? "−" : "+";
    });
  });

  // Gestion des onglets via URL (pour voter.php)
  const urlParams = new URLSearchParams(window.location.search);
  const tab = urlParams.get("tab");
  if (tab) {
    const tabElement = document.getElementById("tab-" + tab);
    if (tabElement) {
      tabElement.click();
    }
  }

  // INIT : désactiver les autres boutons si un vote existe déjà dans une catégorie
  console.log("INIT VOTES STATE");
  ["musique", "chanteur", "groupe"].forEach((type) => {
    const votedBtn = document.querySelector(
      `.btn-vote[data-type-contenu="${type}"][data-voted="1"]`
    );
    console.log("TYPE", type, "votedBtn =", votedBtn);
    if (votedBtn) {
      document
        .querySelectorAll(`.btn-vote[data-type-contenu="${type}"]`)
        .forEach((btn) => {
          btn.disabled = true;
          btn.style.opacity = "0.5";
          btn.dataset.voted = "0";
        });

      votedBtn.disabled = false;
      votedBtn.style.opacity = "0.6";
      votedBtn.dataset.voted = "1";
      votedBtn.innerHTML = "Supprimer mon vote";
    }
  });
});

// Gestion des votes (AJAX)
document.addEventListener("click", function (e) {
  const voteBtn = e.target.closest(".btn-vote");
  if (!voteBtn) return;

  const typeContenu = voteBtn.dataset.typeContenu;
  const contenuId = voteBtn.dataset.contenuId;
  if (!typeContenu || !contenuId) return;

  const alreadyVoted = voteBtn.dataset.voted === "1";
  const mode = alreadyVoted ? "delete" : "vote";

  voteBtn.disabled = true;
  const originalHTML = voteBtn.innerHTML;

  fetch("../vote/vote_handler.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      type_contenu: typeContenu,
      contenu_id: contenuId,
      mode: mode,
    }),
  })
    .then((r) => r.json())
    .then((data) => {
      if (data && data.success) {
        const parent = voteBtn.parentElement;
        const countSpan = parent.querySelector(".vote-count");
        if (countSpan && typeof data.total !== "undefined") {
          countSpan.textContent = data.total;
        }

        if (data.mode === "added") {
          // Désactiver tous les boutons de cette catégorie
          document
            .querySelectorAll(`.btn-vote[data-type-contenu="${typeContenu}"]`)
            .forEach((btn) => {
              btn.disabled = true;
              btn.style.opacity = "0.5";
              btn.dataset.voted = "0";
            });

          // Garder uniquement ce bouton actif pour pouvoir supprimer
          voteBtn.disabled = false;
          voteBtn.style.opacity = "0.6";
          voteBtn.dataset.voted = "1";
          voteBtn.innerHTML = "Supprimer mon vote";
        } else if (data.mode === "deleted") {
          // Tous les boutons redeviennent cliquables
          document
            .querySelectorAll(`.btn-vote[data-type-contenu="${typeContenu}"]`)
            .forEach((btn) => {
              btn.disabled = false;
              btn.style.opacity = "1";
              btn.dataset.voted = "0";

              if (typeContenu === "musique") {
                btn.innerHTML = "❤ Voter pour cette musique";
              } else if (typeContenu === "chanteur") {
                btn.innerHTML = "❤ Voter pour cet artiste";
              } else if (typeContenu === "groupe") {
                btn.innerHTML = "❤ Voter pour ce groupe";
              }
            });
        }
      } else {
        alert(data && data.message ? data.message : "Erreur lors du vote");
        voteBtn.innerHTML = originalHTML;
      }
    })
    .catch((err) => {
      console.error("Vote error", err);
      voteBtn.innerHTML = originalHTML;
    })
    .finally(() => {
      voteBtn.disabled = false;
    });
});

(function () {
  const form = document.querySelector(".search-form");
  const input = document.querySelector(".search-input");
  if (!input) return;
  if (form)
    form.addEventListener("submit", function (e) {
      e.preventDefault();
    });

  let timer = null;
  function performSearch(q) {
    const url = "search_ajax.php?q=" + encodeURIComponent(q);
    fetch(url, { credentials: "same-origin" })
      .then((r) => r.json())
      .then((data) => {
        const paneMus = document.querySelector("#pane-musique .card-list");
        const paneArt = document.querySelector("#pane-artiste .card-list");
        const paneGrp = document.querySelector("#pane-groupe .card-list");
        if (paneMus && data.musiques !== undefined)
          paneMus.innerHTML = data.musiques;
        if (paneArt && data.artistes !== undefined)
          paneArt.innerHTML = data.artistes;
        if (paneGrp && data.groupes !== undefined)
          paneGrp.innerHTML = data.groupes;
      })
      .catch((err) => console.error("Search error", err));
  }

  input.addEventListener("input", function (e) {
    clearTimeout(timer);
    timer = setTimeout(function () {
      performSearch(e.target.value.trim());
    }, 250);
  });
})();