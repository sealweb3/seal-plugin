
export function ensureEnvVar(
	envVar,
	varName
) {
	if (!envVar) {
		throw new Error(`${varName} is not defined`)
	}
	return envVar
}