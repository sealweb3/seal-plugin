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
      chainId: 42161, // Arbitrum Sepolia testnet chain ID
      name: 'arbitrum-sepolia',
      rpcUrls: ['https://sepolia-rollup.arbitrum.io/rpc'],
    },
  });
}

async function createSchemaAndAttestation(data) {
  try {
    const schemaName = 'Institution Schema';
    const schema = {
      name: schemaName,
      data: [
        { name: 'institutionName', type: 'string' },
        { name: 'institutionDescription', type: 'string' },
        { name: 'institutionWebsite', type: 'string' },
        { name: 'institutionWallets', type: 'string' },
      ],
    };

    let schemaResponse = await client.createSchema(schema);
    console.log('Schema created:', schemaResponse);

    if (!schemaResponse.schemaId) {
      throw new Error('Schema creation failed');
    }

    await delay(5000);

    const indexingValue = data.institutionName;

    const attestationResponse = await client.createAttestation({
      schemaId: schemaResponse.schemaId,
      data: data,
      indexingValue: indexingValue,
    });

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
    const institutionName = document.querySelector('input[name="s_seal_entityname"]').value;
    const institutionDescription = document.querySelector('textarea[name="s_seal_entitydescription"]').value;
    const institutionWebsite = document.querySelector('input[name="s_seal_contactwebsite"]').value;
    const institutionWallets = document.querySelector('textarea[name="s_seal_adressList"]').value;

    const data = {
      institutionName: institutionName,
      institutionDescription: institutionDescription,
      institutionWebsite: institutionWebsite,
      institutionWallets: institutionWallets,
    };

    console.log('Creating attestation with data:', data);

    try {
      let attestationId = await createSchemaAndAttestation(data);
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