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

  const studentButton = document.getElementById('studentButton');
  
  if (studentButton) {
    studentButton.addEventListener('click', async function() {
        handleStudentButtonClick(this);
    });
    } else {
        console.error('Student button not found');
    }
    


async function handleStudentButtonClick(button) {
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

  async function sendDataToStudent(action, signature = '', userAddress = '', singMessage = '') {
    console.log('Sending to server:', action, signature, userAddress, singMessage);
    //const messageHash = ethers.hashMessage(message); 
    
    try {
        const response = await fetch(`./js/student.php?courseid=${encodeURIComponent(courseId)}`, { 
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

function resetButton(button) {
    button.disabled = false;
    button.classList.remove('disabledButton');
    button.textContent = config.messages.connectWithMetaMask;
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