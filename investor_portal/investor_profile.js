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
        alert('Photo upload coming soon');
    });
    
    // placegolder button
    document.getElementById('upload-docs')?.addEventListener('click', () => {
        alert('Document uploader coming soon');
    });
    
    // placeholder button
    document.getElementById('deactivate-account')?.addEventListener('click', () => {
        if (confirm('Are you sure you want to deactivate your account? This cannot be undone.')) {
            alert('Account deactivated');
            window.location.href = '../login.html';
        }
    });

    // add funds stuff
    const addFundsBtn = document.getElementById('add-funds-btn');
    const modal = document.getElementById('deposit-modal');
    const cancelBtn = document.getElementById('cancel-deposit-btn');
    const confirmBtn = document.getElementById('confirm-deposit-btn');
    const balanceElement = document.getElementById('investor-balance'); 

    // function to format currency
    const formatCurrency = (value, symbol) => {
        const parts = value.toFixed(2).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return `${symbol} ${parts.join('.')}`;
    };

    // to open the modal (pop up)
    addFundsBtn?.addEventListener('click', () => {
        modal.style.display = 'flex';
    });

    // close the modal
    const closeModal = () => {
        modal.style.display = 'none';
        document.getElementById('bank-account-number').value = '';
        document.getElementById('bank-holder-name').value = '';
        document.getElementById('deposit-amount').value = '';
    };

    // close modal with ancel
    cancelBtn?.addEventListener('click', closeModal);

    // close modal by clicking outside
    modal?.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    confirmBtn?.addEventListener('click', async () => {
    const accountNumber = document.getElementById('bank-account-number').value.trim();
    const holderName = document.getElementById('bank-holder-name').value.trim();
    const amount = parseFloat(document.getElementById('deposit-amount').value);

    if (!accountNumber || !holderName || isNaN(amount) || amount <= 0) {
        alertMessage('Please enter a valid account number, holder name, and deposit amount.', 'error');
        return;
    }

    const currencySymbol = balanceElement.textContent.trim().charAt(0); // Get symbol before fetch

    try {
        const depositData = {
            accountNumber: accountNumber,
            holderName: holderName,
            amount: amount
        };

        const response = await fetch('process_deposit.php', { // Adjust path if needed
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(depositData)
        });

        const result = await response.json();

        if (response.ok && result.success) {
            
            const currentBalanceText = balanceElement.textContent.trim().replace(/[£$,]/g, '').replace(/,/g, '');
            const currentBalance = parseFloat(currentBalanceText);
            const newInvestorBalance = currentBalance + amount;
            
            balanceElement.textContent = formatCurrency(newInvestorBalance, currencySymbol);

            alertMessage(`Transaction successful. Deposited ${formatCurrency(amount, currencySymbol)}. New Investor Balance: ${formatCurrency(newInvestorBalance, currencySymbol)}.`, 'success');
            
            closeModal();

        } else {
            alertMessage(`Deposit failed: ${result.message || 'Unknown server error.'}`, 'error');
        }

    } catch (error) {
        console.error('Fetch error:', error);
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
        const nameField = document.getElementById('full-name');

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
                // Reload the page to display the new data
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