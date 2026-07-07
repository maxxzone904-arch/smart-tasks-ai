document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const btn = document.getElementById('registerBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const errorContainer = document.getElementById('errorContainer');
    const errorMessage = document.getElementById('errorMessage');
    const successContainer = document.getElementById('successContainer');
    const successMessage = document.getElementById('successMessage');
    const form = document.getElementById('registerForm');
    
    // UI Loading state
    btn.disabled = true;
    btnText.innerText = 'Creating account...';
    btnSpinner.classList.remove('hidden');
    errorContainer.classList.add('hidden');
    successContainer.classList.add('hidden');
    
    try {
        const response = await fetch('../api/register.php', {
            method: 'POST',
            body: new FormData(e.target)
        });
        
        if (!response.ok) {
            throw new Error('Server returned an error.');
        }
        
        const result = await response.json();
        
        if (result.status === 'success') {
            successMessage.innerHTML = result.message; // Use innerHTML safely here because the API returns an anchor tag
            successContainer.classList.remove('hidden');
            form.reset();
            form.classList.add('hidden'); // Hide form on success
        } else {
            errorMessage.innerText = result.message;
            errorContainer.classList.remove('hidden');
            btn.disabled = false;
            btnText.innerText = 'Sign up';
            btnSpinner.classList.add('hidden');
        }
    } catch (error) {
        errorMessage.innerText = 'An unexpected network error occurred. Please try again.';
        errorContainer.classList.remove('hidden');
        btn.disabled = false;
        btnText.innerText = 'Sign up';
        btnSpinner.classList.add('hidden');
    }
});
