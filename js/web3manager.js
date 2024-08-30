import axios from 'axios';
import { EvmChains, delegateSignAttestation } from '@ethsign/sp-sdk';
import Cookies from 'js-cookie';
import { ensureEnvVar } from './helpers';
import { ethers } from 'ethers';

//const cookieNameToken = ensureEnvVar(process.env.COOKIE_NAME_TOKEN, 'COOKIE_NAME_TOKEN');

async function login(nonce, userAddress, fullMessage, signature) {
  try {
      const userDto = {
          nonce: nonce,
          address: userAddress,
          message: fullMessage,
          signature: signature
      }
      const response = await axios.post('http://192.46.223.247:4000/auth/login', userDto);
      const stringifiedToken = JSON.stringify(response.data);
      Cookies.set(cookieNameToken, stringifiedToken, { expires: 365 });
  } catch (error) {
      console.error('Error sending data to server:', error);
      return { success: false, error: error.message };
  }
}


async function getSchema(name) {
  try {
    const token = JSON.parse(Cookies.get(cookieNameToken));
    
    const response = await axios.get(`http://192.46.223.247:4000/schemas/getSchemaIdByType/${name}`, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token.access_token}`
      }
    });

    const schemaId = response.data;
    
    return schemaId;
  } catch (error) {
    console.error('Failed to fetch schema:', error.response ? error.response.data : error.message);
    throw error;
  }
}

async function getAccreditorAttestationId() {
  try {
    const token = JSON.parse(Cookies.get(cookieNameToken));

    const response = await axios.get('http://192.46.223.247:4000/attestations/getAccreditorAttestationId', {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token.access_token}`
      }
    });
    const accreditatorAttestationId = response.data;

    return accreditatorAttestationId;
  } catch (error) {
    console.error('Failed to fetch schema:', error.response ? error.response.data : error.message);
    throw error;
  }
}

async function createAttestation(data, schemaId) {
  try {
    const ethereum = window.ethereum
		const provider = new ethers.BrowserProvider(
			ethereum
		)
    const signer = await provider.getSigner()
    const address = await signer.getAddress()

    const attestationInfo = {
      schemaId: schemaId,
      data: data,
      indexingValue: "",
      recipients: [address],
      validUntil: 0
    };
    const info = await delegateSignAttestation(
      attestationInfo,
      {
        chain: EvmChains.arbitrumSepolia,
      }
    )

    return info;
  } catch (error) {
    console.error('Error creating attestation:', error);
    throw error;
  }
}

document.addEventListener('DOMContentLoaded', async function() {
  
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

  const managerButton = document.getElementById('managerButton');
  
  if (managerButton) {
    managerButton.addEventListener('click', async function() {
        handleManagerButtonClick(this);
        console.error('Ready');
    });
    } else {
        console.error('Manager button not found');
    }
    


async function handleManagerButtonClick(button) {
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
            const nonceResponse = await fetch(`/mod/seal/getnonce.php?userAddress=${encodeURIComponent(userAddress)}`);
            console.log(nonceResponse);
            delay(5000); 
            /*const message = config.messages.signMessage;
            const signature = await ethereum.request({
              method: 'personal_sign',
              params: [message, userAddress],
            });
            
            const schemaResponse = await getSchema('course');
            */
          
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
  
  
});