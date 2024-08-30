import axios from 'axios';
import { EvmChains, delegateSignAttestation } from '@ethsign/sp-sdk';

// async function initializeClient() {
//   let privateKey = process.env.PRIVATE_KEY;
//   if (!privateKey.startsWith('0x')) {
//     privateKey = '0x' + privateKey;
//   }
//   if (!/^0x[0-9a-fA-F]{64}$/.test(privateKey)) {
//     throw new Error('Invalid private key format');
//   }
//   // Note: Client initialization code is missing here
// }

async function getSchema() {
  const url = `http://192.46.223.247:4000/schemas/getSchemaIdByType/organization`;
  let jwtToken = process.env.JWT_TOKEN;

  try {
    const response = await axios.get(url, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${jwtToken}`
      }
    });
    console.log('Schema data:', response.data);
    return response.data;
  } catch (error) {
    console.error('Failed to fetch schema:', error.response ? error.response.data : error.message);
    throw error;
  }
}

async function createAttestation(data, schemaId) {
  try {
    const attestationInfo = {
      schemaId: schemaId,
      data: data,
      indexingValue: "",
      recipients: ['0x92388d12744B418eFac8370B266D31fd9C4c5F0e'],
      validUntil: 0
    };
    const info = await delegateSignAttestation(
      attestationInfo,
      {
        chain: EvmChains.arbitrumSepolia,
      }
    )

    console.log('Attestation response:', info);
    return info;
  } catch (error) {
    console.error('Error creating attestation:', error);
    throw error;
  }
}

document.addEventListener('DOMContentLoaded', async function() {

    // await initializeClient();
    const attestationButton = document.getElementById('attestationButton');
  if (attestationButton) {
    attestationButton.addEventListener('click', async function() {
      try {
      const schemaResponse = await getSchema();
      console.log('Schema response:', schemaResponse);

      const institutionName = document.querySelector('input[name="s_seal_entityname"]').value;
      const institutionDescription = document.querySelector('textarea[name="s_seal_entitydescription"]').value;
      const institutionWebsite = document.querySelector('input[name="s_seal_contactwebsite"]').value;
      const institutionWallets = document.querySelector('textarea[name="s_seal_adressList"]').value;

      const data = {
        name: institutionName,
        description: institutionDescription,
        website: institutionWebsite,
        linkedAttestationId: '0x198'
      };

      console.log('Creating attestation with data:', data, institutionWallets);

      // let info = await createAttestation(data, schemaResponse);
      // // console.log('Full info object:', JSON.stringify(info, null, 2));

      // if (!info  !info.attestation  !info.delegationSignature) {
      //   throw new Error('Invalid attestation response structure');
      // }

      // let simplifiedDto = {
      //   attestationDto: info.attestation,
      //   signatureDto: info.delegationSignature,
      //   profileDto: {
      //     name: institutionName,
      //     managers: institutionWallets.split(',').map(wallet => wallet.trim())
      //   }
      // };

      // console.log('Simplified DTO:', JSON.stringify(simplifiedDto, null, 2));
      
      // const account = '0x92388d12744B418eFac8370B266D31fd9C4c5F0e';
      // const url = `http://192.46.223.247:4000/registry/isAuthorizedToCreateProfile/${account}`;
      // let jwtToken = process.env.JWT_TOKEN;
// try { 
      //   const response = await axios.get(url, {
      //     headers: {
      //       'Accept': 'application/json',
      //       'Authorization': Bearer ${jwtToken}
      //     }
      //   });
      // // const url = `http://192.46.223.247:4000/attestations/attestOrganizationInDelegationMode`;
      // // let jwtToken = process.env.JWT_TOKEN;
      // // try { 
      // //   const response = await axios.post(url, simplifiedDto, {
      // //     headers: {
      // //       'Accept': 'application/json',
      // //       'Authorization': Bearer ${jwtToken},
      // //       'Content-Type': 'application/json'
      // //     }
      // //   });
        
      //   console.log('Response data:', response.data);
      // } catch (error) {
      //   console.error('Error sending attestation:', error.response ? error.response.data : error.message);
      // }
    } catch (error) {
      console.error('Error in attestation process:', error);
    } 
    }
    );
    }
  });
