document.addEventListener("DOMContentLoaded", () => {
    const briefBtn = document.getElementById("upm-brief-btn");
    const modal = document.getElementById("upm-brief-modal");
    const closeBtn = modal?.querySelector(".upm-modal-close");
  
    if (briefBtn && modal && closeBtn) {
      // Abrir modal
      briefBtn.addEventListener("click", (e) => {
        e.preventDefault();
        modal.classList.remove("hidden");
        modal.querySelector(".upm-modal-content").classList.add("fade-in-up");
      });
  
      // Cerrar con el botón X
      closeBtn.addEventListener("click", () => {
        modal.classList.add("hidden");
      });
  
      // Cerrar haciendo clic fuera del modal
      modal.addEventListener("click", (e) => {
        if (e.target === modal) {
          modal.classList.add("hidden");
        }
      });
    }
  });