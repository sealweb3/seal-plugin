document.addEventListener('DOMContentLoaded', () => {
  const config = {
      messages: {
          processing: 'Processing...',
          connectWithMetaMask: 'Connect Wallet',
          metamaskNotInstalled: 'MetaMask is not installed. Please consider installing it.',
          connectToMainnet: 'Please connect to the Arbitrum/Sepolia Mainnet',
          requestFailed: 'Request failed!',
          disconnectWallet: 'Disconnect Wallet',
          failedToResetSignature: 'Failed to reset signature',
          signMessage: 'you must sign a message in your wallet to verify that you are the owner or manager of one account/n/n',
          verified: 'Verified'
      }
  };

  const firstButton = document.getElementById('firstButton');
  const studentButton = document.getElementById('studentButton');

  if (firstButton) {
      firstButton.addEventListener('click', async function() {
          handleFirstButtonClick(this);
      });
  } else {
      console.error('First button not found');
  }

  if (studentButton) {
    studentButton.addEventListener('click', async function() {
        handleStudentButtonClick(this);
    });
    } else {
        console.error('Student button not found');
    }

  async function handleFirstButtonClick(button) {
      button.disabled = true;
      button.textContent = config.messages.processing;
      button.classList.add('disabledButton');

      if (typeof window.ethereum !== 'undefined') {
          
          try {
            const nonce = await getNonce();
            console.log('Nonce received:', nonce);

            if (!nonce) {
                alert('Failed to retrieve nonce.');
                resetButton(button);
                return;
            }
              const chainId = await ethereum.request({ method: 'eth_chainId' });
              if (chainId !== '0x66eee') {
                  alert(config.messages.connectToMainnet);
                  resetButton(button);
                  return;
              }
              const accounts = await ethereum.request({ method: 'eth_requestAccounts' });
              const userAddress = accounts[0];
              const message = config.messages.signMessage + nonce;
              const signature = await ethereum.request({
                  method: 'personal_sign',
                  params: [message, userAddress],
              });
              const actionType = button.getAttribute('data-action') || 'action';

              console.log('Sendirver:', actionType);
              const response = await sendDataToServer(actionType, signature, userAddress, message);
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

  

async function handleStudentButtonClick(button) {
    button.disabled = true;
    button.textContent = config.messages.processing;
    button.classList.add('disabledButton');

    if (typeof window.ethereum !== 'undefined') {
        
        try {
            const chainId = await ethereum.request({ method: 'eth_chainId' });
            if (chainId !== '0x66eef') {
                alert(config.messages.connectToMainnet);
                resetButton(button);
                return;
            }
            const accounts = await ethereum.request({ method: 'eth_requestAccounts' });
            const userAddress = accounts[0];
            const message = config.messages.signMessage + nonce;
            const signature = await ethereum.request({
                method: 'personal_sign',
                params: [message, userAddress],
            });
            const actionType = button.getAttribute('data-action') || 'student';

            console.log('Sendirver:', actionType);
            const response = await sendDataToStudent(actionType, signature, userAddress, message);
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

  function resetButton(button) {
      button.disabled = false;
      button.classList.remove('disabledButton');
      button.textContent = config.messages.connectWithMetaMask;
  }

  async function getNonce() {
    try {
        const response = await fetch('../mod/seal/js/nonce.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'getNonce' })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        return data.nonce;

    } catch (error) {
        console.error('Error fetching nonce:', error);
        return null;
    }
  }

  async function sendDataToServer(action, signature = '', userAddress = '', singMessage = '') {
      console.log('Sending to server:', action, signature, userAddress, singMessage);
      //const messageHash = ethers.hashMessage(message); 
      
      try {
          const response = await fetch('../mod/seal/js/web3.php', { 
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ action, signature, userAddress, singMessage, messagehash: singMessage }),
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

  async function sendDataToStudent(action, signature = '', userAddress = '', singMessage = '') {
    console.log('Sending to server:', action, signature, userAddress, singMessage);
    //const messageHash = ethers.hashMessage(message); 
    
    try {
        const response = await fetch('../mod/seal/js/student.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action, signature, userAddress, singMessage }),
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
      }const messageHash = ethers.hashMessage(message); 
  }
});