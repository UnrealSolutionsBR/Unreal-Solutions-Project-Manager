document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('upm-request-modal');
    const openBtn = document.getElementById('upm-open-request');
    const closeBtn = document.getElementById('upm-close-request');
    const form = document.getElementById('upm-request-form');
  
    if (openBtn && modal && closeBtn && form) {
      openBtn.addEventListener('click', () => {
        modal.classList.add('open');
      });
  
      closeBtn.addEventListener('click', () => {
        modal.classList.remove('open');
      });
  
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
  
        const type = document.getElementById('upm-request-type').value;
        const message = document.getElementById('upm-request-message').value;
        const projectId = parseInt(form.dataset.projectId);
  
        try {
          const res = await fetch(upm_request_ajax.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
              action: 'upm_submit_request',
              nonce: upm_request_ajax.nonce,
              project_id: projectId,
              request_type: type,
              message: message,
            }),
          });
  
          const json = await res.json();
  
          if (json.success) {
            alert('✅ ' + json.data.message);
            modal.classList.remove('open');
            form.reset();
          } else {
            alert('❌ ' + json.data.message);
          }
        } catch (err) {
          alert('❌ Error de red');
        }
      });
    }
  });
  