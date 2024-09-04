import axios from 'axios';
import { SiweMessage } from 'siwe';
import { EvmChains, delegateSignAttestation } from '@ethsign/sp-sdk';
import Cookies from 'js-cookie';
import { ensureEnvVar } from './helpers';
import { ethers } from 'ethers';

const cookieNameToken = ensureEnvVar(process.env.COOKIE_NAME_TOKEN, 'COOKIE_NAME_TOKEN');

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

async function getOrganization(name) {
  try {
    const token = JSON.parse(Cookies.get(cookieNameToken));
    
    const response = await axios.get(`http://192.46.223.247:4000/attestations/getOrganizationAttestationIdByOrganizationName/${name}`, {
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

function createAttestationStudent(schemaId, dataAttes, organizationId) {
  const address = dataAttes.wallets;
  const names = dataAttes.names;
  const data = dataAttes.seals;
  console.log('data:', data);
  const datalink = {
    name: data[0].name,
    description: data[0].description,
    duration: data[0].duration,
    location: data[0].location,
    partners: data[0].partners,
    linkedAttestationId: organizationId,
  };
  const attestationInfo = {
    schemaId: schemaId,
    validUntil: 0,
    recipients: [address],
    indexingValue: "",
    data: datalink,
  };
  return attestationInfo;
}

function createAttestationStudentBackend(schemaId, dataAttes, organizationId) {
  const address = dataAttes.wallets;
  const names = dataAttes.names;
  const data = dataAttes.seals;
  const attestationInfo = {
    schemaId: schemaId,
    validUntil: 0,
    recipients: [address],
    names: [names],
    indexingValue: "",
    data: data,
    link: organizationId,
  };

  return attestationInfo;
}

document.addEventListener('DOMContentLoaded', async function() {
  
  const config = {
    messages: {
      processing: 'Processing...',
      connectWithMetaMask: 'Connect Wallet',
      metamaskNotInstalled: 'MetaMask is not installed. Please consider installing it.',
      connectToMainnet: 'Please connect to the Arbitrum/Sepolia Mainnet',
    }
  };

  const managerButton = document.getElementById('managerButton');
  
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

  if (managerButton) {
    managerButton.addEventListener('click', async function() {
      handleManagerButtonClick(this);
    });
  } else {
      console.error('Manager button not found');
  }

async function handleManagerButtonClick(button) {
    button.disabled = true;
    button.textContent = config.messages.processing;
    button.classList.add('disabledButton');

    if (typeof window.ethereum !== 'undefined') {
      console.log("course:", courseId);
      
      try {
        const chainId = await ethereum.request({ method: 'eth_chainId' });
            if (chainId !== '0x66eee') {
                alert(config.messages.connectToMainnet);
                resetButton(button);
                return;
            }
            //const accounts = await ethereum.request({ method: 'eth_requestAccounts' });
            const provider = new ethers.BrowserProvider(ethereum);
            const signer = await provider.getSigner();
            const userAddress = await signer.getAddress();
            const nonceResponse = await fetch(`./getnonce.php?userAddress=${encodeURIComponent(userAddress)}`);
            if (!nonceResponse.ok) {
                throw new Error('Failed to fetch nonce');
            }
            const nonceData = await nonceResponse.json();
            const nonce = nonceData.nonce;
            const fullMessage = createSiweMessage(userAddress, nonce);
            const signature = await signer.signMessage(fullMessage);
            await login(nonce, userAddress, fullMessage, signature);
            const schemaCourse = await getSchema('course');
            const schemaOrganization = await getOrganization('test');
            console.log('SchemaOrganization:', schemaOrganization);
            const dataCertify = await fetch(`./getdata.php?courseid=${encodeURIComponent(courseId)}`);
            const dataCerti = await dataCertify.json();
            const dataAttestation = createAttestationStudent(schemaCourse, dataCerti, schemaOrganization);
            console.log('DataAttestation:', dataAttestation);

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