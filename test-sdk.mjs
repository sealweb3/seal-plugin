import {
  SignProtocolClient,
  SpMode,
  EvmChains,
  OffChainSignType
} from '@ethsign/sp-sdk';
import { privateKeyToAccount } from 'viem/accounts';

async function testSDK() {
  // Replace with a valid 32-byte hexadecimal private key
  const privateKey = '0x1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef';
  const client = new SignProtocolClient(SpMode.OffChain, {
      signType: OffChainSignType.EvmEip712,
      account: privateKeyToAccount(privateKey), // optional
  });

  try {
      // Create schema
      const schemaInfo = await client.createSchema({
          name: 'Test Schema',
          data: [{ name: 'name', type: 'string' }],
      });
      console.log('Schema created:', schemaInfo);

      // Create attestation
      const attestationInfo = await client.createAttestation({
          schemaId: schemaInfo.schemaId, // Use the created schemaId
          data: { name: 'Test Name' },
          indexingValue: 'Test Indexing Value',
      });
      console.log('Attestation created:', attestationInfo);

      // Revoke attestation
      const attestationId = attestationInfo.attestationId;
      const revokeAttestationRes = await client.revokeAttestation(attestationId, {
          reason: 'Test reason',
      });
      console.log('Attestation revoked:', revokeAttestationRes);
  } catch (error) {
      console.error('Error:', error);
  }
}

testSDK();