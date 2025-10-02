function alertMessage(message, type) {
    const messageBox = document.getElementById('messageBox');
    const messageContent = document.getElementById('messageContent');
    
    // reset classes
    messageBox.classList.remove('success', 'error');
    messageBox.style.display = 'block';
    
    if (type === 'success') {
        messageBox.classList.add('success');
        messageContent.innerHTML = `✅ **Success!** ${message}`;
    } else {
        messageBox.classList.add('error');
        messageContent.innerHTML = `❌ **Error:** ${message}`;
    }

    // logging for deep debugging
    const icon = type === 'success' ? '✅' : '❌';
    console.log(`${icon} [${type.toUpperCase()}] Server Response: ${message}`);
}

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

window.showConfirmation = showConfirmation; 

/**
 * Executes the API call to cancel the investment.
 * @param {string} investmentId - ID of investment to cancel.
 */
const deleteInvestment = async (investmentId) => {
    try {
        // You're using alertMessage from the current file, which is fine
        alertMessage('Processing cancellation...', 'success'); 
        
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
            // The existing my_investments.js logic reloads the page, which is what you want.
            // It ensures the investment list and current amount on the page are updated.
            setTimeout(() => window.location.reload(), 1500); 
        } else {
            alertMessage(result.message, 'error');
        }

    } catch (error) {
        console.error('Cancellation failed:', error);
        alertMessage('An unexpected error occurred during cancellation.', 'error');
    }
};
// Attach to window scope for visibility inside the DOMContentLoaded listener
window.deleteInvestment = deleteInvestment;
document.addEventListener('DOMContentLoaded', () => {
    
    const pitchIdElement = document.getElementById('pitch-id');
    const investAmountInput = document.getElementById('invest-amount');
    const investForm = document.getElementById('invest-form');
    const confirmBtn = document.getElementById('confirm-btn');
    const cancelBtn = document.getElementById('cancel-investment');
    
    if (!investForm) {
         console.error("CRITICAL ERROR: Investment form (ID: 'invest-form') not found. Submission will fail.");
         return;
    }

    const PITCH_ID = pitchIdElement ? parseInt(pitchIdElement.value, 10) : 0;
    
    let investmentIdElement = document.getElementById('investment-id');
    let INVESTMENT_ID = investmentIdElement ? parseInt(investmentIdElement.value, 10) : 0;
    
    const detectedTierSpan = document.getElementById('detected-tier');
    const detectedMultSpan = document.getElementById('detected-mult');
    const calcSharesSpan = document.getElementById('calc-shares');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const buttonText = document.getElementById('buttonText');

    // is investable?
    const isInvestable = investAmountInput && !investAmountInput.disabled;

    // tiers from php table
    const tiers = [];
    document.querySelectorAll('#tiers-table tbody tr').forEach(row => {
        tiers.push({
            name: row.getAttribute('data-tier'),
            min: parseFloat(row.getAttribute('data-min')),
            max: parseFloat(row.getAttribute('data-max')),
            multiplier: parseFloat(row.getAttribute('data-mult'))
        });
    });

    const updateInvestmentDetails = () => {
        const amount = parseFloat(investAmountInput.value) || 0;
        
        // if the amount is too low or the pitch is closed
        if (amount < 1 || !isInvestable) {
            detectedTierSpan.textContent = '—';
            detectedMultSpan.textContent = '—';
            calcSharesSpan.textContent = '—';
            // Only disable if not investable OR amount is too low
            confirmBtn.disabled = !isInvestable || amount < 1; 
            return;
        }

        // find the matching tier based on the amount
        let selectedTier = tiers.find(t => amount >= t.min && (amount <= t.max || t.max >= 9999999));
        
        if (selectedTier) {
            const shares = Math.round(amount * selectedTier.multiplier);
            
            detectedTierSpan.textContent = selectedTier.name;
            detectedMultSpan.textContent = selectedTier.multiplier.toFixed(1);
            calcSharesSpan.textContent = shares.toLocaleString(); 
            
            confirmBtn.disabled = false;
            confirmBtn.setAttribute('data-shares', shares);

        } else {
            // if the amount is outside any defined tier range
            const minAllowed = tiers.length > 0 ? `£${tiers[0].min}` : 'N/A';
            detectedTierSpan.textContent = `N/A (Min ${minAllowed})`;
            detectedMultSpan.textContent = 'N/A';
            calcSharesSpan.textContent = '0';
            confirmBtn.disabled = true;
            confirmBtn.removeAttribute('data-shares');
        }
    };

    if (investAmountInput) {
        investAmountInput.addEventListener('input', updateInvestmentDetails);
        updateInvestmentDetails(); // Initial calculation
    }

    investForm.addEventListener('submit', async (e) => {
        e.preventDefault(); 
        
        // hide previous message
        document.getElementById('messageBox').style.display = 'none';

        if (PITCH_ID === 0 || isNaN(PITCH_ID)) {
            return alertMessage('Invalid Pitch ID detected. Cannot proceed.', 'error');
        }

        // checks before submission
        if (!isInvestable) return alertMessage('Pitch is not currently open for investment.', 'error');
        
        const amount = parseFloat(investAmountInput.value);
        const shares = confirmBtn.getAttribute('data-shares');
        
        if (amount <= 0 || !shares || parseInt(shares, 10) <= 0) {
            return alertMessage('Please enter a valid amount and ensure shares are calculated.', 'error');
        }

        const actionType = INVESTMENT_ID > 0 ? 'Update' : 'New';

        // turn off UI elements and show loading state
        confirmBtn.disabled = true;
        buttonText.textContent = actionType === 'Update' ? 'Updating...' : 'Confirming...';
        loadingSpinner.style.display = 'block';
        if (cancelBtn) cancelBtn.disabled = true;

        try {
            const maxRetries = 3;
            let response = null;
            let result = null;

            const postBody = new URLSearchParams({
                pitch_id: PITCH_ID, 
                investment_id: INVESTMENT_ID, 
                amount: amount,
                shares: shares
            });

            for (let i = 0; i < maxRetries; i++) {
                try {
                    response = await fetch('process_investment.php', {
                        method: 'POST',
                        body: postBody
                    });

                    if (!response.ok) {
                        try {
                            const errorResult = await response.json();
                            throw new Error(errorResult.message || `Server returned status: ${response.status}`);
                        } catch (jsonError) {
                            throw new Error(`Server returned status: ${response.status} (Non-JSON response)`);
                        }
                    }

                    result = await response.json();
                    break;
                } catch (error) {
                    if (i < maxRetries - 1) {
                        const delay = Math.pow(2, i) * 1000;
                        await new Promise(resolve => setTimeout(resolve, delay));
                    } else {
                        throw error;
                    }
                }
            }

            if (result && result.success) {
                alertMessage(result.message, 'success');
                
                if (actionType === 'New' && result.investment_id) {
                    INVESTMENT_ID = result.investment_id;
                    investmentIdElement.value = INVESTMENT_ID;
                    
                    buttonText.textContent = 'Update Investment';
                    if (cancelBtn) {
                        cancelBtn.disabled = false;
                        cancelBtn.setAttribute('data-investment-id', INVESTMENT_ID);
                    }
                }

                // debug
                console.log('Investment confirmed. Initiating page reload...');

                // reload page after a short delay to show updated data
                setTimeout(() => {
                    window.location.reload(); 
                }, 500);

            } else if (result) {
                alertMessage(result.message || 'Investment failed with an unknown error.', 'error');
            } else {
                alertMessage('Failed to process investment due to a network or server issue (Empty response).', 'error');
            }

        } catch (error) {
            console.error('Investment transaction failed:', error);
            alertMessage(error.message || 'An unexpected error occurred. Check your connection or try again later.', 'error');
            
        } finally {
            confirmBtn.disabled = false;
            buttonText.textContent = actionType === 'Update' ? 'Update Investment' : 'Confirm Investment';
            loadingSpinner.style.display = 'none';
            if (cancelBtn && INVESTMENT_ID > 0) cancelBtn.disabled = false;
        }
    });

    cancelBtn?.addEventListener('click', () => {
        if (!window.showConfirmation || !window.deleteInvestment) {
            return alertMessage('Cancellation utility functions are not available.', 'error');
        }

        if (!isInvestable || cancelBtn.disabled) {
            return alertMessage('Cannot cancel: Pitch is closed or you have no active investment.', 'error');
        }

        const investmentIdToCancel = cancelBtn.getAttribute('data-investment-id');

        if (investmentIdToCancel) {
            window.showConfirmation(
                'Are you sure you want to permanently cancel this investment? This action cannot be undone and the funds will be returned to your balance.',
                () => window.deleteInvestment(investmentIdToCancel)
            );
        } else {
            alertMessage('No active investment ID found to cancel.', 'error');
        }
    });
});
