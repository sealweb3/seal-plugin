import axios from 'axios';
import { EvmChains, delegateSignAttestation } from '@ethsign/sp-sdk';
import Cookies from 'js-cookie';
import { ensureEnvVar } from './helpers';
import { ethers } from 'ethers';

const cookieNameToken = ensureEnvVar(process.env.COOKIE_NAME_TOKEN, 'COOKIE_NAME_TOKEN');

async function getSchema() {
  try {
    const token = JSON.parse(Cookies.get(cookieNameToken));
    
    const response = await axios.get('http://192.46.223.247:4000/schemas/getSchemaIdByType/organization', {
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
  
  document.getElementById('attestationButton').addEventListener('click', async function() {
    try {
      const schemaResponse = await getSchema();
      const institutionName = document.querySelector('input[name="s_seal_entityname"]').value;
      const institutionDescription = document.querySelector('textarea[name="s_seal_entitydescription"]').value;
      const institutionWebsite = document.querySelector('input[name="s_seal_contactwebsite"]').value;
      const institutionWallets = document.querySelector('textarea[name="s_seal_adressList"]').value;

      const accreditatorAttestationId = await getAccreditorAttestationId();

      const data = {
        name: institutionName,
        description: institutionDescription,
        website: institutionWebsite,
        linkedAttestationId: accreditatorAttestationId
      };


      let info = await createAttestation(data, schemaResponse);
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
      const url = `http://192.46.223.247:4000/attestations/attestOrganizationInDelegationMode`;
      let jwtToken = process.env.JWT_TOKEN;
      try { 
        const response = await axios.post(url, simplifiedDto, {
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${jwtToken}`,
            'Content-Type': 'application/json'
          }
        });
        
      } catch (error) {
        console.error('Error sending attestation:', error.response ? error.response.data : error.message);
      }
    } catch (error) {
      console.error('Error in attestation process:', error);
    }
  });
});