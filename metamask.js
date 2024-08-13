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
            const ethereum = window.ethereum;
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
    
                // message hash

                function toUtf8Bytes(str) {
                    const utf8 = [];
                    for (let i = 0; i < str.length; i++) {
                        let charcode = str.charCodeAt(i);
                        if (charcode < 0x80) utf8.push(charcode);
                        else if (charcode < 0x800) {
                            utf8.push(0xc0 | (charcode >> 6), 0x80 | (charcode & 0x3f));
                        } else if (charcode < 0xd800 || charcode >= 0xe000) {
                            utf8.push(0xe0 | (charcode >> 12), 0x80 | ((charcode >> 6) & 0x3f), 0x80 | (charcode & 0x3f));
                        } else {
                            i++;
                            charcode = 0x10000 + (((charcode & 0x3ff) << 10) | (str.charCodeAt(i) & 0x3ff));
                            utf8.push(0xf0 | (charcode >> 18), 0x80 | ((charcode >> 12) & 0x3f), 0x80 | ((charcode >> 6) & 0x3f), 0x80 | (charcode & 0x3f));
                        }
                    }
                    return utf8;
                }
                
                function bytesToHex(bytes) {
                    return bytes.map(byte => byte.toString(16).padStart(2, '0')).join('');
                }
                
                function keccak256(message) {
                    return '0x' + bytesToHex(toUtf8Bytes('keccak256 hash of ' + message));
                }
                
                function hashMessage(message) {
                    const messageBytes = toUtf8Bytes(message);
                    const messageHex = bytesToHex(messageBytes);
                    return keccak256(messageHex);
                }
        
                const hashedMessage = hashMessage(message);
        

                // Fetch nonce from the server using GET
                const nonceResponse = await fetch(`/mod/seal/getnonce.php?userAddress=${encodeURIComponent(userAddress)}`);
                if (!nonceResponse.ok) {
                    throw new Error('Failed to fetch nonce');
                }
                const nonceData = await nonceResponse.json();
                const nonce = nonceData.nonce;
    
                const messageWithNonce = `${message}.  Here is your unique nonce: ${nonce}`;
                const signature = await ethereum.request({
                    method: 'personal_sign',
                    params: [messageWithNonce, userAddress]
                });
                const response = await sendDataToServer(messageWithNonce, hashedMessage, signature, userAddress);
                console.log('Response from server:', response);
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

    async function sendDataToServer(messageWithNonce, signature, userAddress) {
        console.log('Sending to server:', messageWithNonce, signature, userAddress);
        try {
            const response = await fetch('/mod/seal/sendvalidation.php', { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ messageWithNonce, signature, userAddress }),
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