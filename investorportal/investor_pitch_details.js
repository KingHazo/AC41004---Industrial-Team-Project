document.addEventListener('DOMContentLoaded', () => {
    // variables from php
    const PITCH_ID = document.getElementById('pitch-id')?.value;
    let investmentIdElement = document.getElementById('investment-id');
    let INVESTMENT_ID = investmentIdElement ? parseInt(investmentIdElement.value, 10) : 0;
    
    const investAmountInput = document.getElementById('invest-amount');
    const investForm = document.getElementById('invest-form');
    const confirmBtn = document.getElementById('confirm-btn');
    const cancelBtn = document.getElementById('cancel-investment');
    
    const detectedTierSpan = document.getElementById('detected-tier');
    const detectedMultSpan = document.getElementById('detected-mult');
    const calcSharesSpan = document.getElementById('calc-shares');
    
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

    // investment calc
    const updateInvestmentDetails = () => {
        const amount = parseFloat(investAmountInput.value) || 0;
        
        // if the amount is too low or the pitch is closed
        if (amount < 1 || !isInvestable) {
            detectedTierSpan.textContent = '—';
            detectedMultSpan.textContent = '—';
            calcSharesSpan.textContent = '—';
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
        updateInvestmentDetails();
    }

    investForm?.addEventListener('submit', async (e) => {
        e.preventDefault();

        // checks before submission
        if (!isInvestable) return alertMessage('Pitch is not currently open for investment.', 'error');
        
        const amount = parseFloat(investAmountInput.value);
        const shares = confirmBtn.getAttribute('data-shares');
        
        if (amount <= 0 || !shares) {
            return alertMessage('Please enter a valid amount to proceed.', 'error');
        }

        const actionType = INVESTMENT_ID > 0 ? 'Update' : 'New';

        // turn off UI elements and show loading state
        confirmBtn.disabled = true;
        confirmBtn.textContent = actionType === 'Update' ? 'Updating...' : 'Confirming...';
        if (cancelBtn) cancelBtn.disabled = true;

        try {
            const maxRetries = 3;
            let response = null;
            let result = null;

            for (let i = 0; i < maxRetries; i++) {
                try {
                    response = await fetch('process_investment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            pitch_id: PITCH_ID,
                            investment_id: INVESTMENT_ID, // 0 for new investment
                            amount: amount,
                            shares: shares
                        })
                    });

                    if (response.ok) {
                        result = await response.json();
                        break;
                    } else {
                        throw new Error(`Server returned status: ${response.status}`);
                    }
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
                    
                    confirmBtn.textContent = 'Update Investment';
                    if (cancelBtn) {
                        cancelBtn.disabled = false;
                        cancelBtn.setAttribute('data-investment-id', INVESTMENT_ID);
                    }
                }

                // reload the page to refresh balance/progress bar
                setTimeout(() => window.location.reload(), 1500); 

            } else if (result) {
                alertMessage(result.message || 'Investment failed with an unknown error.', 'error');
            } else {
                alertMessage('Failed to process investment due to a network or server issue.', 'error');
            }

        } catch (error) {
            console.error('Investment transaction failed:', error);
            alertMessage('An unexpected error occurred. Check your connection or try again later.', 'error');
            
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.textContent = actionType === 'Update' ? 'Update Investment' : 'Confirm Investment';
            if (cancelBtn && INVESTMENT_ID > 0) cancelBtn.disabled = false;
        }
    });

    cancelBtn?.addEventListener('click', () => {
        if (typeof showConfirmation !== 'function' || typeof deleteInvestment !== 'function') {
             return alertMessage('Cancellation utility functions are not available.', 'error');
        }

        if (!isInvestable || cancelBtn.disabled) {
            return alertMessage('Cannot cancel: Pitch is closed or you have no active investment.', 'error');
        }

        const investmentIdToCancel = cancelBtn.getAttribute('data-investment-id');

        if (investmentIdToCancel) {
            showConfirmation(
                'Are you sure you want to permanently cancel this investment? This action cannot be undone and the funds will be returned to your balance.',
                () => deleteInvestment(investmentIdToCancel)
            );
        } else {
             alertMessage('No active investment ID found to cancel.', 'error');
        }
    });
});
