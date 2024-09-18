import { SiweMessage } from 'siwe';
import { ethers } from 'ethers'; 
import Cookies from 'js-cookie';
import { ensureEnvVar } from './helpers';
import axios from 'axios';

const cookieNameToken = ensureEnvVar(process.env.COOKIE_NAME_TOKEN, 'COOKIE_NAME_TOKEN');

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
                const nonceResponse = await fetch(`../mod/seal/getnonce.php?userAddress=${encodeURIComponent(userAddress)}`);
                if (!nonceResponse.ok) {
                    throw new Error('Failed to fetch nonce');
                }
                const nonceData = await nonceResponse.json();
                const nonce = nonceData.nonce;
                const fullMessage = createSiweMessage(userAddress, nonce);
                console.log('Full message:', fullMessage);

                const signature = await signer.signMessage(fullMessage);
                await login(nonce, userAddress, fullMessage, signature);
                const profilesAndAuthorizations = await getProfilesAndAuthorizations(userAddress);
                await sendResponseToSettings(profilesAndAuthorizations);
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
    
    async function sendResponseToSettings(data) {
        console.log('data:', data)
        const authori = data[0];
        const profile = data[1];
        
        try {
            const response = await fetch('/moodle/mod/seal/js/web3.php', { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ authori, profile }),
            });
    
            const responseText = await response.text();
            console.log('Raw response from server:', responseText);
    
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
    
            return JSON.parse(responseText);
        } catch (error) {
            console.error('Error sending data to server:', error);
            return { success: false, error: error.message };
        }   
    }
    

    async function login(nonce, userAddress, fullMessage, signature) {
        try {
            const userDto = {
                nonce: nonce,
                address: userAddress,
                message: fullMessage,
                signature: signature
            }
            const response = await axios.post(`${url}/auth/login`, userDto);
            const stringifiedToken = JSON.stringify(response.data);
            Cookies.set(cookieNameToken, stringifiedToken, { expires: 365 });
        } catch (error) {
            console.error('Error sending data to server:', error);
            return { success: false, error: error.message };
        }
    }

    async function getProfilesAndAuthorizations(address) {
        try {
            const token = JSON.parse(Cookies.get(cookieNameToken));
            console.log('Token:', token);
            const response = await axios.get(`${url}/profiles/getProfilesAndIsAuthorized/${address}`, {
                headers: {
                    'ngrok-skip-browser-warning': 'true',
                    'Authorization': `Bearer ${token.access_token}`
                }
            });
            const profilesAndAuthorizations = response.data;
            console.log('Profiles and authorizations:', profilesAndAuthorizations);
            return profilesAndAuthorizations;
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


    function updateView() {
    console.log('Updating view');
        setTimeout(() => {
            location.reload(); 
        }, 500); 
    }
});