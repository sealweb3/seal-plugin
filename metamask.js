document.addEventListener('DOMContentLoaded', () => {
  const config = {
      messages: {
          processing: 'Processing...',
          connectWithMetaMask: 'Connect with MetaMask',
          metamaskNotInstalled: 'MetaMask is not installed. Please consider installing it.',
          connectToMainnet: 'Please connect to the Arbitrum/Sepolia Mainnet',
          requestFailed: 'Request failed!',
          disconnectWallet: 'Disconnect Wallet',
          failedToResetSignature: 'Failed to reset signature',
          signMessage: 'Please sign this message to request your certificate.',
          verified: 'Verified'
      }
  };

  const metamaskButton = document.getElementById('metamaskButton');
  const disconnectButton = document.getElementById('disconnectButton');

  if (metamaskButton) {
      metamaskButton.addEventListener('click', async function() {
          handleMetaMaskButtonClick(this);
      });
  } else {
      console.error('MetaMask button not found');
  }

  if (disconnectButton) {
      console.log('Disconnect button found');
      disconnectButton.addEventListener('click', async function() {
          handleDisconnectButtonClick();
      });
  } else {
      console.error('Disconnect button not found');
  }

  async function handleMetaMaskButtonClick(button) {
      button.disabled = true;
      button.textContent = config.messages.processing;
      button.classList.add('disabledButton');

      if (typeof window.ethereum !== 'undefined') {
          try {
              const chainId = await ethereum.request({ method: 'eth_chainId' });
              if (chainId !== '0x66eee') {
                  alert(config.messages.connectToMainnet);
                  resetButton(button);
                  return;
              }
              const accounts = await ethereum.request({ method: 'eth_requestAccounts' });
              const userAddress = accounts[0];
              const message = config.messages.signMessage;
              const signature = await ethereum.request({
                  method: 'personal_sign',
                  params: [message, userAddress],
              });
              const actionType = button.getAttribute('data-action') || 'verifyAdmin';

              // console.log('Sendirver:', actionType);
              const response = await sendDataToServer(actionType, signature, userAddress);
              console.log('Response from server:', response);
              updateView(response);
          } catch (error) {
              console.error('Error during MetaMask interaction:', error);
              resetButton(button);
          }
      } else {
          alert(config.messages.metamaskNotInstalled);
          resetButton(button);
      }
  }

  async function handleDisconnectButtonClick() {
      console.log('Disconnecting wallet');
      const response = await sendDataToServer('reset');
      console.log('Response from server:', response);
      if (response.success) {
          location.reload(); // Reload the page to update the view
      } else {
          alert(config.messages.failedToResetSignature);
      }
  }

  function resetButton(button) {
      button.disabled = false;
      button.classList.remove('disabledButton');
      button.textContent = config.messages.connectWithMetaMask;
  }

  async function sendDataToServer(action, signature = '', userAddress = '') {
      console.log('Sending to server:', action, signature, userAddress);
      try {
          const response = await fetch('/mod/seal/metamasksignature.php', { 
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ action, signature, userAddress }),
          });
          if (!response.ok) {
              throw new Error('Network response was not ok');
          }
          return await response.json();
      } catch (error) {
          console.error('Error sending data to server:', error);
          return { success: false, error: error.message };
      }
  }

  function updateView(response) {
      if (response.success) {
          location.reload(); // Reload the page to update the view
      } else {
          alert(config.messages.requestFailed);
          resetButton(document.getElementById('metamaskButton'));
      }
  }
});