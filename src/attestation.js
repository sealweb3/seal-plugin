import { SignProtocolClient, SpMode, OffChainSignType, IndexService } from '@ethsign/sp-sdk';
import { privateKeyToAccount } from 'viem/accounts';

let client;

function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function initializeClient() {
  let privateKey = process.env.PRIVATE_KEY;
  if (!privateKey.startsWith('0x')) {
    privateKey = '0x' + privateKey;
  }
  if (!/^0x[0-9a-fA-F]{64}$/.test(privateKey)) {
    throw new Error('Invalid private key format');
  }

  client = new SignProtocolClient(SpMode.OffChain, {
    signType: OffChainSignType.EvmEip712,
    account: privateKeyToAccount(privateKey),
    chain: {
      chainId: 42161,
      name: 'arbitrum-sepolia',
      rpcUrls: ['https://sepolia-rollup.arbitrum.io/rpc'],
    },
  });
}

async function getSchema() {
  const url = `http://192.46.223.247:4000/schemas/getSchemaIdByType/organization`;
  let jwtToken = process.env.JWT_TOKEN;

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${jwtToken}`
      },
    });
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.text(); 
    console.log('Schema data:', data);
    return data;
  } catch (error) {
    console.error('Failed to fetch schema:', error);
    throw error;
  }
}

async function createAttestation(data, schemaId) {
  try {
    // const attestation = await client.createAttestation()
    
    const attestation = await delegateSignAttestation({        
      schemaId: schemaId,
      data: data,
      indexingValue: data.institutionName,
    })

    // validUntil: 0,        
    // recipients: wallets,
    // name: 'Desarrollo de Aplicaciones Onchain Parte I',          
    // description: El curso «Desarrollo de Aplicaciones Onchain Parte I» tuvo una duración de siete semanas, desde el 7 de marzo 
    //    hasta el 9 de mayo de 2024. Con más de 20 horas y 10 sesiones de formación intensiva, se centró en la enseñanza de la sintaxis de Solidity y conceptos esenciales de blockchain, proporcionando una comprensión sólida del ecosistema Ethereum y sus capas dos',          duration: '20 horas',
    // location: 'Online',       
    // partners: ['Ethereum Foundation, Arbitrum Foundation, UxTic, Ethereum Bogotá'],
    // inkedAttestationId: '0x0'        }
    // } as Attestation)
    // const info: AttestationDelegationSignature =        await delegateSignAttestation(JSON.parse(attestation), {
    //     chain: EvmChains.arbitrumSepolia        })
    // const formAttestationCourseDto = {
    //   attestationDto: info.attestation,        signatureDto: info.delegationSignature,


    // const attestationResponse = await client.createAttestation({
    //   schemaId: data.schemaId,
    //   data: data,
    //   indexingValue: indexingValue,
    // });

    console.log('Attestation response:', attestationResponse);
    return attestationResponse.attestationId; // Ensure the correct property is returned
  } catch (error) {
    console.error('Error creating schema or attestation:', error);
    throw error;
  }
}


async function getAttestationFromIndexService(attestationId) {
  const indexService = new IndexService('testnet');
  const res = await client.getAttestation(attestationId);
  return res;
}

document.addEventListener('DOMContentLoaded', async function() {
  await initializeClient(); 
  
  document.getElementById('attestationButton').addEventListener('click', async function() {
    const schemaResponse = await getSchema();
    const institutionName = document.querySelector('input[name="s_seal_entityname"]').value;
    const institutionDescription = document.querySelector('textarea[name="s_seal_entitydescription"]').value;
    const institutionWebsite = document.querySelector('input[name="s_seal_contactwebsite"]').value;
    const institutionWallets = document.querySelector('textarea[name="s_seal_adressList"]').value;

    const data = {
      institutionName: institutionName,
      institutionDescription: institutionDescription,
      institutionWebsite: institutionWebsite,
      institutionWallets: institutionWallets
    };

    console.log('Creating attestation with data:', data);

    try {
      let attestationId = await createAttestation(data, schemaResponse);
      await delay(3000); 
      const attestation = await getAttestationFromIndexService(attestationId);
      if (attestation) {
        console.log('Attestation successfully retrieved:', attestation);
      } else {
        console.error('Attestation not found in index service');
      }
    } catch (error) {
      console.error('Error creating attestation:', error);
    }
  });
});