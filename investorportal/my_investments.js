/**
 * show a custom confirmation modal.
 * @param {string} message - confirmation message text.
 * @param {function} onConfirm - execute if the user confirms.
 */
const showConfirmation = (message, onConfirm) => {
    // remove any existing modal
    const existingModal = document.getElementById('custom-confirm-modal');
    if (existingModal) existingModal.remove();

    const modalHtml = `
        <div id="custom-confirm-modal" style="
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.6); display: flex; justify-content: center; 
            align-items: center; z-index: 1000; font-family: 'Montserrat', sans-serif;">
            <div style="background: white; padding: 30px; border-radius: 12px; 
                        box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-width: 400px; width: 90%;">
                <h4 style="margin-top: 0; color: #1f2937;">Confirm Cancellation</h4>
                <p style="margin-bottom: 20px; color: #4b5563;">${message}</p>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button id="cancel-no" style="padding: 10px 15px; border: 1px solid #d1d5db; 
                            border-radius: 8px; background: #f9fafb; cursor: pointer; color: #4b5563;">No, Keep It</button>
                    <button id="cancel-yes" style="padding: 10px 15px; border: none; border-radius: 8px; 
                            background: #ef4444; color: white; cursor: pointer;">Yes, Cancel</button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = document.getElementById('custom-confirm-modal');

    document.getElementById('cancel-no').onclick = () => modal.remove();
    document.getElementById('cancel-yes').onclick = () => {
        modal.remove();
        onConfirm();
    };
};

/**
 * temporary custom feedback message.
 * @param {string} message - message content.
 * @param {string} type - success or error
 */
const alertMessage = (message, type) => {
    let msgElement = document.getElementById('feedback-message');
    if (!msgElement) {
        msgElement = document.createElement('div');
        msgElement.id = 'feedback-message';
        document.body.appendChild(msgElement);
    }
    
    // styling
    const bgColor = type === 'success' ? '#10b981' : '#ef4444';
    msgElement.style.cssText = `
        position: fixed; top: 10px; right: 10px; padding: 15px 25px; 
        background: ${bgColor}; color: white; border-radius: 8px; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1001; opacity: 1; 
        transition: opacity 0.5s ease-in-out; font-family: 'Montserrat', sans-serif;
    `;
    msgElement.textContent = message;

    // hide after 3 seconds
    setTimeout(() => {
        msgElement.style.opacity = '0';
        setTimeout(() => msgElement.remove(), 500);
    }, 3000);
};

/**.
 * @param {string} investmentId - ID of investment to cancel.
 */

const deleteInvestment = async (investmentId) => {
    try {
        const response = await fetch('cancel_investment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `investment_id=${investmentId}` 
        });

        const result = await response.json();
        
        if (result.success) {
            alertMessage(result.message, 'success');
            // reload page to show the updated list and balance after deletion
            setTimeout(() => window.location.reload(), 1500); 
        } else {
            alertMessage(result.message, 'error');
        }

    } catch (error) {
        console.error('Cancellation failed:', error);
        alertMessage('An unexpected error occurred during cancellation.', 'error');
    }
};


const chips = document.querySelectorAll('.chip');
const list = document.getElementById('investments');

function applyFilter(status) {
    Array.from(list.children).forEach(card => {
        const s = card.getAttribute('data-status'); // active | funded | closed
        const show = (status === 'all') ? true : (s === status);
        card.style.display = show ? '' : 'none';
    });
}

chips.forEach(c => {
    c.addEventListener('click', () => {
        chips.forEach(x => x.classList.remove('active'));
        c.classList.add('active');
        applyFilter(c.dataset.filter);
    });
});

applyFilter('all');


list.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;

    const id = btn.dataset.id;
    const action = btn.dataset.action;

    if (action === 'view') {
        // update to pass the correct PitchID when implemented. !!!!!! :O
        window.location.href = 'investor_pitch_details.html';
    }

    if (action === 'cancel') {
        const card = btn.closest('.inv-card');
        const status = card?.getAttribute('data-status');

        if (status !== 'active') {
            alertMessage('You can only cancel while the pitch is still active.', 'error');
            return;
        }

        const investmentId = btn.dataset.id;
        if (investmentId) {
            // confirmation modal which which calls deleteInvestment
            showConfirmation(
                'Are you sure you want to permanently cancel this investment? This action cannot be undone and the funds will be returned to your balance.',
                () => deleteInvestment(investmentId)
            );
        }
    }
});
