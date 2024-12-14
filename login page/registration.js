

function selectPlan(planId) {
    // Remove selected class from all plans
    // document.querySelectorAll('.plan-card').forEach(card => {
    //     card.classList.remove('selected');
    // });
    
    // Add selected class to chosen plan
    const selectedPlan = document.querySelector(`.plan-card input[value="${planId}"]`).parentElement;
    selectedPlan.classList.add('selected');
    
    // Select the radio button
    const radio = selectedPlan.querySelector('input[type="radio"]');
    radio.checked = true;
    
    // Update total amount
    const price = planPrices[planId];
    document.getElementById('totalAmount').textContent = price.toFixed(2);
}

// Show/hide card details based on payment method selection
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const cardDetails = document.getElementById('card-details');
        if (this.value === 'credit_card' || this.value === 'debit_card') {
            cardDetails.style.display = 'block';
        } else {
            cardDetails.style.display = 'none';
        }
    });
});

document.getElementById('registrationForm').onsubmit = async function(event) {
    event.preventDefault();
    
    try {
        // Show loading state
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = 'Processing...';

        const formData = new FormData(this);
        const response = await fetch('http://localhost:3000/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(formData)
        });

        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.details || 'Registration failed');
        }

        const result = await response.text();
        
        try {
            // Try to parse as JSON
            const jsonResult = JSON.parse(result);
            if (jsonResult.error) {
                throw new Error(jsonResult.error);
            }
        } catch (e) {
            // If not JSON or has error, check if it's a redirect
            if (result.includes('Location:')) {
                const redirectUrl = result.split('Location: ')[1].split('\n')[0];
                window.location.href = redirectUrl;
                return;
            }
        }

        // If we get here, assume success and redirect
        window.location.href = 'registration_success.php';

    } catch (error) {
        console.error('Error:', error);
        alert('Registration error: ' + error.message);
    }
};