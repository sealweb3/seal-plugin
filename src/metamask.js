import { SiweMessage } from 'siwe';
import { ethers } from 'ethers'; // Import utils from ethers

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
            verified: 'Verified'
        }
    };

    const metamaskButton = document.getElementById('metamaskButton');
    const disconnectButton = document.getElementById('disconnectButton');

    function createSiweMessage(address, nonce) {
        const message = new SiweMessage({
            domain: 'Seal',
            address: address,
            statement: 'Seal attestation',
            uri: 'https://sealweb3.com',
            version: '1',
            chainId: 421614,
            nonce: nonce,
            issuedAt: new Date().toISOString(),
        });
        return message.prepareMessage();
    }
    
    if (metamaskButton) {
        metamaskButton.addEventListener('click', async function() {
            handleMetaMaskButtonClick(this);
        });
    } 

    if (disconnectButton) {
        disconnectButton.addEventListener('click', async function() {
            handleDisconnectButtonClick();
        });
    } 

    async function handleMetaMaskButtonClick(button) {
        button.disabled = true;
        button.textContent = config.messages.processing;
        button.classList.add('disabledButton');
    
        if (typeof window.ethereum !== 'undefined') {
            const ethereum = window.ethereum;
            try {
                const chainId = await ethereum.request({ method: 'eth_chainId' });
                if (chainId !== '0x66eee') {
                    alert(config.messages.connectToMainnet);
                    resetButton(button);
                    return;
                }
                // const accounts = await ethereum.request({ method: 'eth_requestAccounts' });
                const provider = new ethers.BrowserProvider(ethereum);
                const signer = await provider.getSigner();
                const userAddress = await signer.getAddress();

                // Fetch nonce from the server using GET
                const nonceResponse = await fetch(`/mod/seal/getnonce.php?userAddress=${encodeURIComponent(userAddress)}`);
                if (!nonceResponse.ok) {
                    throw new Error('Failed to fetch nonce');
                }
                const nonceData = await nonceResponse.json();
                const nonce = nonceData.nonce;
                const fullMessage = createSiweMessage(userAddress, nonce);
                console.log('Full message:', fullMessage);

                const signature = await signer.signMessage(fullMessage);
                const response = await sendDataToServer(nonce, userAddress, fullMessage, signature);
                await sendResponseToSettings(response);
                updateView();

            } catch (error) {
                console.error('Error during MetaMask interaction:', error);
                resetButton(button);
            }
        } else {
            alert(config.messages.metamaskNotInstalled);
            resetButton(button);
        }
    }
    
    async function sendResponseToSettings(response) {
        try {
            const settingsResponse = await fetch('/mod/seal/settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(response),
            });
            if (!settingsResponse.ok) {
                throw new Error('Failed to send response to settings.php');
            }
            console.log('Response sent to settings.php successfully', response);
        } catch (error) {
            console.error('Error sending response to settings.php:', error);
        }
    }

    async function sendDataToServer(nonce, userAddress, fullMessage, signature) {
        console.log('Sending to server:', nonce, userAddress, fullMessage, signature);
        try {
            const response = await fetch('/mod/seal/sendvalidation.php', { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nonce, userAddress, fullMessage, signature }),
            });
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error sending data to server:', error);
            return { success: false, error: error.message };
        }
    }

    async function handleDisconnectButtonClick() {
        console.log('Disconnecting wallet');
        try {
            const response = await fetch('/mod/seal/settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'reset' }),
            });
    
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
    
            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                throw new Error('Failed to parse JSON response: ' + text);
            }
    
            if (result.status === 'success') {
                console.log('Authorization reset successfully:', result.message);
                location.reload(); 
            } else {
                console.error('Failed to reset authorization:', result.message);
            }
        } catch (error) {
            console.error('Error during disconnect:', error);
        }
    }

    function resetButton(button) {
        button.disabled = false;
        button.classList.remove('disabledButton');
        button.textContent = config.messages.connectWithMetaMask;
    }


    function updateView() {
    console.log('Updating view');
        setTimeout(() => {
            location.reload(); // Reload the page to update the view
        }, 1000); // 
    }
});