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
    logo.src = "../icons/logos/MyPulse_White-removebg-preview.png";
  } else {
    navbar.classList.remove("scrolled");
    logo.src = "../icons/logos/MyPulse-removebg-preview.png";
  }
});

// Music covers auto-scroll
document.addEventListener("DOMContentLoaded", function () {
  const scrollContent = document.querySelector(".scroll-content");
  if (scrollContent) {
    // Duplicate the content for infinite scroll
    const originalContent = scrollContent.innerHTML;
    scrollContent.innerHTML += originalContent;
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const audio = document.getElementById("vote-audio-player");
  let currentBtn = null;
  let currentProgressBar = null;
  let currentProgressFill = null;
  let currentPlayPauseBtn = null;

  function setBtnState(btn, isPlaying) {
    const container = btn.closest(".audio-player-container");
    const progressContainer = container.querySelector(
      ".progress-bar-container"
    );
    const playBtn = container.querySelector(".btn-play-audio");

    if (isPlaying) {
      playBtn.style.display = "none";
      progressContainer.style.display = "flex";
      currentProgressBar = progressContainer.querySelector(".progress-bar-bg");
      currentProgressFill =
        progressContainer.querySelector(".progress-bar-fill");
      currentPlayPauseBtn = progressContainer.querySelector(".btn-play-pause");
      currentPlayPauseBtn.textContent = "⏸";
      // Ajouter les classes du bouton "Écouter" SANS modifier la classe existante
      currentPlayPauseBtn.classList.add(...playBtn.classList);
    } else {
      playBtn.style.display = "block";
      progressContainer.style.display = "none";
    }
  }

  function updateProgress() {
    if (currentProgressFill && audio.duration) {
      const progress = (audio.currentTime / audio.duration) * 100;
      currentProgressFill.style.width = progress + "%";
    }
  }

  // Audio + transformation en barre de progression
  document.querySelectorAll(".btn-play-audio").forEach((btn) => {
    btn.addEventListener("click", () => {
      const src = btn.getAttribute("data-audio");
      if (!src) return;

      if (currentBtn === btn && !audio.paused) {
        audio.pause();
        setBtnState(btn, false);
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

  // Gestionnaire pour la barre de progression
  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("progress-bar-bg")) {
      const rect = e.target.getBoundingClientRect();
      const clickX = e.clientX - rect.left;
      const width = rect.width;
      const percentage = (clickX / width) * 100;
      audio.currentTime = (percentage / 100) * audio.duration;
    }
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

  // Clic en dehors du lecteur audio pour réinitialiser
  document.addEventListener("click", (e) => {
    if (currentBtn && !e.target.closest(".audio-player-container")) {
      audio.pause();
      setBtnState(currentBtn, false);
      currentBtn = null;
    }
  });

  audio.addEventListener("timeupdate", updateProgress);

  audio.addEventListener("ended", () => {
    if (currentBtn) {
      setBtnState(currentBtn, false);
      currentBtn = null;
    }
  });

  // Bouton + / - description
  document.querySelectorAll(".toggle-desc-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".content-card");
      const desc = card.querySelector(".content-card-description");
      if (!desc) return;

      const isShown = desc.classList.toggle("show");
      btn.textContent = isShown ? "−" : "+";
    });
  });

  // Gestion des onglets via URL
  const urlParams = new URLSearchParams(window.location.search);
  const tab = urlParams.get("tab");
  if (tab) {
    const tabElement = document.getElementById("tab-" + tab);
    if (tabElement) {
      tabElement.click();
    }
  }
});
