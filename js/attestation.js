import axios from 'axios';
import { EvmChains, delegateSignAttestation } from '@ethsign/sp-sdk';
import Cookies from 'js-cookie';
import { ensureEnvVar } from './helpers';
import { ethers } from 'ethers';

const cookieNameToken = ensureEnvVar(process.env.COOKIE_NAME_TOKEN, 'COOKIE_NAME_TOKEN');

async function getSchema() {
  try {
    const token = JSON.parse(Cookies.get(cookieNameToken));
    
    const response = await axios.get(`${url}/schemas/getSchemaIdByType/organization`, {
      headers: {
        'Ngrok-Skip-Browser-Warning': 'true',
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

async function getAccreditorAttestationId() {
  try {
    const token = JSON.parse(Cookies.get(cookieNameToken));

    const response = await axios.get(`${url}/attestations/getAccreditorAttestationId`, {
      headers: {
        'Ngrok-Skip-Browser-Warning': 'true',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token.accessToken}`
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

async function sendResponseToSetting(profileId) {
  console.log('profileId', profileId)
  try {
      const response = await fetch(`${dirurl}/js/attestation.php`, { 
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ profileId }),
      });

      const responseText = await response.text();
      console.log('Raw response from attestation.php:', responseText);

      if (!response.ok) {
          throw new Error('Network response was not ok');
      }

      return JSON.parse(responseText);
  } catch (error) {
      console.error('Error sending data to server:', error);
      return { success: false, error: error.message };
  }   
}

document.addEventListener('DOMContentLoaded', async function() {
  console.log('Attestation JS loaded correctly.');  // Añadir esta línea para comprobar
  try {
    const schemaResponse = await getSchema();
    console.log('Schema:', schemaResponse);
    
    const accreditatorAttestationId = await getAccreditorAttestationId();
    console.log('acreditator:', accreditatorAttestationId);
    
    const data = {
      name: institutionName,
      description: institutionDescription,
      website: institutionWebsite,
      linkedAttestationId: accreditatorAttestationId
    };  
    
    let info = await createAttestation(data, schemaResponse);
    console.log('info attestation:', info);
    const url2 = `${url}/attestations/attestOrganizationInDelegationMode`;
    console.log('url: ', url2)
    
    if (!info || !info.attestation || !info.delegationSignature) {
        throw new Error('Invalid attestation response structure');
    }
        
    let simplifiedDto = {
      attestationDto: info.attestation,
      signatureDto: info.delegationSignature,
      profileDto: {
        name: institutionName,
        managers: institutionWallets.split(',').map(wallet => wallet.trim())
      }
    };
            
    console.log('Simplified DTO:', JSON.stringify(simplifiedDto, null, 2));
    let jwtToken = process.env.JWT_TOKEN;
    try { 
      const response = await axios.post(url2, simplifiedDto, {
        headers: {
          'ngrok-skip-browser-warning': 'true',
          'Accept': 'application/json',
          'Authorization': `Bearer ${jwtToken}`,
          'Content-Type': 'application/json'
        }
      });
      const profileres = response.data
      console.log('Response: ', profileres);
      await sendResponseToSetting(profileres);
                  
    } catch (error) {
      console.error('Error sending attestation:', error.response ? error.response.data : error.message);
    }
    updateView();
  } catch (error) {
    console.error('Error in attestation process:', error);
  }

  function updateView() {
    console.log('Updating view');
        setTimeout(() => {
            location.reload(); 
        }, 500); 
    }
});