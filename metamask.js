document.addEventListener('DOMContentLoaded', () => {
  const metamaskButton = document.getElementById('metamaskButton');
  const disconnectButton = document.getElementById('disconnectButton');

  if (metamaskButton) {
      metamaskButton.addEventListener('click', async function() {
          this.disabled = true; 
          this.textContent = 'Processing...'; 
          this.classList.add('disabledButton'); 

          if (typeof window.ethereum !== 'undefined') {
              try {
                  const chainId = await ethereum.request({ method: 'eth_chainId' });
                  if (chainId !== '0x66eee') {
                      alert('Please connect to the Arbitrum/Sepolia Mainnet');
                      resetButton(this);
                      return;
                  }
                  const accounts = await ethereum.request({ method: 'eth_requestAccounts' });
                  const userAddress = accounts[0];
                  const message = "Please sign this message to request your certificate.";
                  const signature = await ethereum.request({
                      method: 'personal_sign',
                      params: [message, userAddress],
                  });
                  console.log('Sending data to server:', { signature, userAddress });
                  const response = await sendDataToServer('verify', signature, userAddress);
                  console.log('Response from server:', response);
                  updateView(response);
              } catch (error) {
                  console.error('Error during MetaMask interaction:', error);
                  resetButton(this);
              }
          } else {
              alert('MetaMask is not installed. Please consider installing it.');
              resetButton(this);
          }
      });
  } else {
      console.error('MetaMask button not found');
  }

  if (disconnectButton) {
      console.log('Disconnect button found');
      disconnectButton.addEventListener('click', async function() {
          console.log('Disconnecting wallet');
          const response = await sendDataToServer('reset');
          console.log('Response from server:', response);
          if (response.success) {
              location.reload(); // Reload the page to update the view
          } else {
              alert('Failed to reset signature');
          }
      });
  } else {
      console.error('Disconnect button not found');
  }

  function resetButton(button) {
      button.disabled = false;
      button.classList.remove('disabledButton');
      button.textContent = 'Connect with MetaMask';
  }

  async function sendDataToServer(action, signature = '', userAddress = '') {
      console.log('Sending to server:', action, signature, userAddress);
      const response = await fetch('/mod/seal/metamasksignature.php', { // Adjust the path as needed
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action, signature, userAddress }),
      });
      return await response.json();
  }

  function updateView(response) {
      if (response.success) {
          location.reload(); // Reload the page to update the view
      } else {
          alert('Request failed!');
          resetButton(document.getElementById('metamaskButton'));
      }
  }
});