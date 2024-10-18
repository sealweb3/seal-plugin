import { SiweMessage } from 'siwe';
import { ethers } from 'ethers';
import Cookies from 'js-cookie';
import { ensureEnvVar } from './helpers';
import axios from 'axios';
import { EvmChains, delegateSignAttestation } from '@ethsign/sp-sdk';

const cookieNameToken = ensureEnvVar(process.env.COOKIE_NAME_TOKEN, 'COOKIE_NAME_TOKEN');

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


async function getSchema(name) {
  try {
    const token = JSON.parse(Cookies.get(cookieNameToken));
    
    const response = await axios.get(`${url}/schemas/getSchemaIdByType/${name}`, {
      headers: {
        'ngrok-skip-browser-warning': 'true',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token.accessToken}`
      }
    });

    const schemaId = response.data;
    
    return schemaId;
  } catch (error) {
    console.error('Failed to fetch schema:', error.response ? error.response.data : error.message);
    throw error;
  }
}

function createAttestationStudent(schemaId, dataAttes) {
  const address = dataAttes.wallets;
  const names = dataAttes.names;
  const data = dataAttes.seals;
  console.log('data:', data);
  const datalink = {
    name: data[0].name,
    description: data[0].description,
    duration: data[0].duration,
    location: data[0].location,
    partners: [data[0].partners],
    linkedAttestationId: program,
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

document.addEventListener('DOMContentLoaded', () => {
  
  const config = {
    messages: {
      processing: 'Processing...',
      connectWithMetaMask: 'Connect Wallet',
      metamaskNotInstalled: 'MetaMask is not installed. Please consider installing it.',
      connectToMainnet: 'Please connect to the '+name_web3,
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
        chainId: var_chain,
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

  async function sendResponseToView(atest, ids, courseNow) {
    console.log('atest', atest)
    try {
        const response = await fetch(`${dirurl}/js/manager.php`, { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ atest, ids, courseNow }),
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

async function handleManagerButtonClick(button) {
    button.disabled = true;
    button.textContent = config.messages.processing;
    button.classList.add('disabledButton');

    const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
    let selectedIds = [];

    // Loop through all the checked checkboxes and push their values (user IDs) to the array
    checkboxes.forEach((checkbox) => {
        selectedIds.push(checkbox.value);
    });

    // Create JSON from the array
    const selectedData = JSON.stringify({ selectedUsers: selectedIds });
    console.log('Selected Users:', selectedData);

    if (typeof window.ethereum !== 'undefined') {
      const ethereum = window.ethereum;
      try {
        const provider = new ethers.BrowserProvider(ethereum);
        const signer = await provider.getSigner();
        const userAddress = await signer.getAddress();
        const network = await provider.getNetwork();
        const chainId = network.chainId;  // Chain ID (e.g., 1 for Ethereum Mainnet, 42161 for Arbitrum One, etc.)
        const networkName = network.name;
        if (BigInt(var_chain) !== chainId) {
          alert(config.messages.connectToMainnet);
          resetButton(button);
          return;
        }

        const nonceResponse = await fetch(`${dirurl}/getnonce.php?userAddress=${encodeURIComponent(userAddress)}`);
        if (!nonceResponse.ok) {
            throw new Error('Failed to fetch nonce');
        }
        const nonceData = await nonceResponse.json();
        const nonce = nonceData.nonce;
        const fullMessage = createSiweMessage(userAddress, nonce);
        const signature = await signer.signMessage(fullMessage);
        await login(nonce, userAddress, fullMessage, signature);
        const schemaCourse = await getSchema('course');                                                   
        const dataCertify = await fetch(`${dirurl}/getdata.php?courseid=${encodeURIComponent(courseId)}`, {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json' // Specify the content type as JSON
          },
          body: selectedData// Send the JSON object in the request body
        });
        console.log('datacertify:', dataCertify);
        const dataCerti = await dataCertify.json();
        const dataAttestation = createAttestationStudent(schemaCourse, dataCerti);
        console.log('DataAttestation:', dataAttestation);
        const info = await delegateSignAttestation(
          dataAttestation,
          {
            chain: evmchain,
          }
        )
        console.log('EvmChain Arbitrum: ', EvmChains.arbitrum);
        console.log('EvmChain base sepolia: ', EvmChains.baseSepolia);
        console.log('EvmChain Base', EvmChains.base);
        console.log('Info:', info);
        const urlatest = url+'/attestations/attestCourseInDelegationMode';
        const token = JSON.parse(Cookies.get(cookieNameToken));
        console.log('token:', token);
        const resfile = await fetch(file1);
        const blob = await resfile.blob();
        const filebadge = new File([blob], 'badge.jpeg', { type: blob.type });
        const resfile2 = await fetch(file2);
        const blob2 = await resfile2.blob();
        const certificateFile = new File([blob2], 'certificate.jpeg', { type: blob.type });
        console.log('Badge: ', file1);
        console.log('Certificate: ', file2);
        //crear formData
        const formCourseDto = new FormData();
        formCourseDto.append('badge', filebadge);
        formCourseDto.append('certificate', certificateFile);
        formCourseDto.append('name', dataCerti.seals[0].name);
        formCourseDto.append('description', dataCerti.seals[0].description);
        formCourseDto.append('participants', JSON.stringify(dataCerti.names));
        formCourseDto.append('wallets', JSON.stringify(dataCerti.wallets));
        
        const formAttestationCourseDto = new FormData();
        formAttestationCourseDto.append('attestationDto', JSON.stringify(info.attestation));    
        formAttestationCourseDto.append('signatureDto', JSON.stringify(info.delegationSignature));      
        formAttestationCourseDto.append('profileIdDto', profileid); 
        formCourseDto.forEach((value, key) => {formAttestationCourseDto.append(key, value)});
        for (let [key, value] of formAttestationCourseDto.entries()) {
          console.log(key, value);
        }
        try { 
          const response = await axios.post(urlatest, formAttestationCourseDto, {
            headers: {
              'ngrok-skip-browser-warning': 'true',
              'Authorization': `Bearer ${token.accessToken}`,
              'Content-Type': 'multipart/form-data',
            }
          });
          console.log('responde attestation: ', response.data);
          await sendResponseToView(response.data, dataCerti.ids, courseId);
          updateView();
          
        } catch (error) {
          console.error('Error sending attestation:', error.response ? error.response.data : error.message);
        }
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
 
function updateView() {
  console.log('Updating view');
      setTimeout(() => {
          location.reload(); 
      }, 500); 
  }
  
});