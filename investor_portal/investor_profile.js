const alertMessage = (message, type) => {
    const alertBox = document.createElement('div');
    alertBox.textContent = message;
    alertBox.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 10px 20px; 
        background-color: ${type === 'success' ? '#4CAF50' : '#f44336'}; 
        color: white; z-index: 10000; border-radius: 5px; opacity: 1; transition: opacity 0.5s;
    `;
    document.body.appendChild(alertBox);
    setTimeout(() => {
        alertBox.style.opacity = '0';
        setTimeout(() => alertBox.remove(), 500);
    }, 3000);
};

document.addEventListener('DOMContentLoaded', () => {
    const displaySection = document.getElementById('display-details');
    const editForm = document.getElementById('inline-edit-form');
    
    // switch to edit button
    const editDetailsButton = document.getElementById('edit-details-btn');
    const cancelButton = document.getElementById('cancel-edit-btn'); 
    
    const editProfileButton = document.getElementById('edit-profile'); 
    
    // placeholder button
    document.getElementById('change-photo')?.addEventListener('click', () => {
        alertMessage('Photo upload coming soon', 'info');
    });
    
    // placegolder button
    document.getElementById('upload-docs')?.addEventListener('click', () => {
        alertMessage('Document uploader coming soon', 'info');
    }); 
    
    // placeholder button
    document.getElementById('deactivate-account')?.addEventListener('click', () => {
        alertMessage('Please contact support to deactivate your account securely.', 'info');
    });

    const balanceElement = document.getElementById('investor-balance'); 

    const addFundsBtn = document.getElementById('add-funds-btn');
    const depositModal = document.getElementById('deposit-modal');
    const cancelDepositBtn = document.getElementById('cancel-deposit-btn');
    const confirmDepositBtn = document.getElementById('confirm-deposit-btn');

    const withdrawFundsBtn = document.getElementById('withdraw-funds-btn');
    const withdrawalModal = document.getElementById('withdrawal-modal');
    const cancelWithdrawalBtn = document.getElementById('cancel-withdrawal-btn');
    const confirmWithdrawalBtn = document.getElementById('confirm-withdrawal-btn');
    
    // function to format currency
    const formatCurrency = (value, symbol) => {
        const parts = value.toFixed(2).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return `${symbol}${symbol !== '' && symbol !== ' ' ? ' ' : ''}${parts.join('.')}`;
    };

    const getCurrentBalanceDetails = () => {
        const text = balanceElement.textContent.trim();
        const currencySymbol = text.match(/([£$€])/)?.[0] || '£';
        const currentBalanceText = text.replace(/[^0-9.-]+/g, '').replace(/,/g, '');
        const currentBalance = parseFloat(currentBalanceText);
        return { currentBalance, currencySymbol };
    };

    // generic function to close and clear a modal
    const closeModal = (modal) => {
        if (modal === depositModal) {
            document.getElementById('bank-account-number').value = '';
            document.getElementById('bank-holder-name').value = '';
            document.getElementById('deposit-amount').value = '';
        } else if (modal === withdrawalModal) {
            document.getElementById('withdraw-bank-account-number').value = '';
            document.getElementById('withdraw-bank-holder-name').value = '';
            document.getElementById('withdrawal-amount').value = '';
        }
        modal.style.display = 'none';
    };

    addFundsBtn?.addEventListener('click', () => {
        depositModal.style.display = 'flex';
    });
    cancelDepositBtn?.addEventListener('click', () => closeModal(depositModal));
    depositModal?.addEventListener('click', (e) => {
        if (e.target === depositModal) {
            closeModal(depositModal);
        }
    });

    confirmDepositBtn?.addEventListener('click', async () => {
        const accountNumber = document.getElementById('bank-account-number').value.trim();
        const holderName = document.getElementById('bank-holder-name').value.trim();
        const amount = parseFloat(document.getElementById('deposit-amount').value);
        const { currencySymbol } = getCurrentBalanceDetails();

        if (!accountNumber || !holderName || isNaN(amount) || amount <= 0) {
            alertMessage('Please enter a valid account number, holder name, and deposit amount.', 'error');
            return;
        }

        try {
            const depositData = {
                accountNumber: accountNumber,
                holderName: holderName,
                amount: amount
            };

            const response = await fetch('process_deposit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(depositData)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                
                const { currentBalance } = getCurrentBalanceDetails();
                const newInvestorBalance = currentBalance + amount;
                
                balanceElement.textContent = formatCurrency(newInvestorBalance, currencySymbol);

                alertMessage(`Transaction successful. Deposited ${formatCurrency(amount, currencySymbol)}. New Investor Balance: ${formatCurrency(newInvestorBalance, currencySymbol)}.`, 'success');
                
                closeModal(depositModal);

            } else {
                alertMessage(`Deposit failed: ${result.message || 'Unknown server error.'}`, 'error');
            }

        } catch (error) {
            console.error('Fetch error:', error);
            alertMessage('Network error. Could not connect to the server.', 'error');
        }
    });
    
    withdrawFundsBtn?.addEventListener('click', () => {
        withdrawalModal.style.display = 'flex';
    });
    cancelWithdrawalBtn?.addEventListener('click', () => closeModal(withdrawalModal));
    withdrawalModal?.addEventListener('click', (e) => {
        if (e.target === withdrawalModal) {
            closeModal(withdrawalModal);
        }
    });

    confirmWithdrawalBtn?.addEventListener('click', async () => {
        const accountNumber = document.getElementById('withdraw-bank-account-number').value.trim();
        const holderName = document.getElementById('withdraw-bank-holder-name').value.trim();
        const amount = parseFloat(document.getElementById('withdrawal-amount').value);
        const { currentBalance, currencySymbol } = getCurrentBalanceDetails();

        if (!accountNumber || !holderName || isNaN(amount) || amount <= 0) {
            alertMessage('Please enter a valid account number, holder name, and withdrawal amount.', 'error');
            return;
        }
        
        if (amount > currentBalance) {
            alertMessage('Withdrawal failed: Insufficient funds in Investor Balance.', 'error');
            return;
        }

        try {
            const withdrawalData = {
                accountNumber: accountNumber,
                holderName: holderName,
                amount: amount
            };

            const response = await fetch('process_withdrawal.php', { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(withdrawalData)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                
                const newInvestorBalance = currentBalance - amount;
                
                balanceElement.textContent = formatCurrency(newInvestorBalance, currencySymbol);

                alertMessage(`Transaction successful. Withdrew ${formatCurrency(amount, currencySymbol)}. New Investor Balance: ${formatCurrency(newInvestorBalance, currencySymbol)}.`, 'success');
                
                closeModal(withdrawalModal);

            } else {
                alertMessage(`Withdrawal failed: ${result.message || 'Unknown server error.'}`, 'error');
            }

        } catch (error) {
            console.error('Withdrawal fetch error:', error);
            alertMessage('Network error. Could not connect to the server.', 'error');
        }
    });

    const switchToEditMode = () => {
        displaySection.style.display = 'none';
        editForm.style.display = 'block';
    };

    const switchToDisplayMode = () => {
        displaySection.style.display = 'block';
        editForm.style.display = 'none';
    };


    editProfileButton?.addEventListener('click', switchToEditMode);
    
    editDetailsButton?.addEventListener('click', switchToEditMode);

    cancelButton?.addEventListener('click', switchToDisplayMode);


    editForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const saveButton = editForm.querySelector('button[type="submit"]');
        const originalText = saveButton.textContent;
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';

        const formData = new URLSearchParams(new FormData(editForm));

        try {
            const response = await fetch('update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alertMessage(result.message, 'success');
                // reload the page to display the new data
                setTimeout(() => window.location.reload(), 500);

            } else {
                alertMessage(result.message, 'error');
            }

        } catch (error) {
            console.error('Profile update failed:', error);
            alertMessage('An unexpected error occurred. Please try again.', 'error');
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = originalText;
        }
    });
});
