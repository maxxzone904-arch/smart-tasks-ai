document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault(); // Stop standard form submission
    
    const btn = document.getElementById('loginBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const errorContainer = document.getElementById('errorContainer');
    const errorMessage = document.getElementById('errorMessage');
    
    // Set UI to loading state
    btn.disabled = true;
    btnText.innerText = 'Logging in...';
    btnSpinner.classList.remove('hidden');
    errorContainer.classList.add('hidden');
    
    try {
        const response = await fetch('../api/login.php', {
            method: 'POST',
            body: new FormData(e.target)
        });
        
        if (!response.ok) {
            throw new Error('Server returned an error.');
        }
        
        const result = await response.json();
        
        if (result.status === 'success') {
            window.location.href = 'index'; // Redirect smoothly
        } else {
            // Show dynamic error without reloading page
            errorMessage.innerText = result.message;
            errorContainer.classList.remove('hidden');
            
            // Reset button state
            btn.disabled = false;
            btnText.innerText = 'Log in';
            btnSpinner.classList.add('hidden');
        }
    } catch (error) {
        errorMessage.innerText = 'An unexpected network error occurred. Please try again.';
        errorContainer.classList.remove('hidden');
        
        btn.disabled = false;
        btnText.innerText = 'Log in';
        btnSpinner.classList.add('hidden');
    }
});
