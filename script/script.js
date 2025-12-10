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
  let currentProgressBar, currentProgressFill, currentProgressTime, currentPlayPauseBtn;

  function setBtnState(btn, isPlaying) {
    const container = btn.closest('.audio-player-container');
    const progressContainer = container.querySelector('.progress-bar-container');
    const playBtn = container.querySelector('.btn-play-audio');

    if (isPlaying) {
        playBtn.style.display = 'none';
        progressContainer.style.display = 'flex';
        currentProgressBar = progressContainer.querySelector('.progress-bar-bg');
        currentProgressFill = progressContainer.querySelector('.progress-bar-fill');
        currentProgressTime = progressContainer.querySelector('.progress-time');
        currentPlayPauseBtn = progressContainer.querySelector('.btn-play-pause');
        currentPlayPauseBtn.textContent = '⏸';
        currentPlayPauseBtn.classList.add(...playBtn.classList);
    } else {
        playBtn.style.display = 'block';
        progressContainer.style.display = 'none';
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
