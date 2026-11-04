export const loadEnvironment = (__ENV.LOAD_ENVIRONMENT || '').toLowerCase();
export const baseUrl = (__ENV.BASE_URL || '').replace(/\/$/, '');
export const allowMutations = (__ENV.ALLOW_MUTATIONS || '').toLowerCase() === 'true';

if (!['local', 'staging'].includes(loadEnvironment)) {
  throw new Error('LOAD_ENVIRONMENT must be explicitly set to local or staging.');
}

if (!baseUrl) {
  throw new Error('BASE_URL is required.');
}

const parsedUrl = new URL(baseUrl);
const localHosts = ['127.0.0.1', 'localhost', 'host.docker.internal'];
const allowedStagingHosts = (__ENV.STAGING_HOST_ALLOWLIST || '')
  .split(',')
  .map((host) => host.trim().toLowerCase())
  .filter(Boolean);
const isLocal = localHosts.includes(parsedUrl.hostname.toLowerCase());
const isAllowedStaging = allowedStagingHosts.includes(parsedUrl.hostname.toLowerCase());

if (!isLocal && !isAllowedStaging) {
  throw new Error('Refusing to run: add the exact staging hostname to STAGING_HOST_ALLOWLIST.');
}

if (loadEnvironment === 'local' && !isLocal) {
  throw new Error('LOAD_ENVIRONMENT=local may target only localhost.');
}

export const targets = {
  p50: Number(__ENV.TARGET_HTTP_P50_MS || 300),
  p95: Number(__ENV.TARGET_HTTP_P95_MS || 800),
  p99: Number(__ENV.TARGET_HTTP_P99_MS || 1500),
  errorRate: Number(__ENV.TARGET_ERROR_RATE || 0.01),
};

export function tokens(name) {
  return (__ENV[name] || '')
    .split(',')
    .map((token) => token.trim())
    .filter(Boolean);
}

export function tokenFor(values) {
  return values.length === 0 ? null : values[(__VU - 1) % values.length];
}

export function mutatingScenarioEnabled(name, flag, values) {
  const enabled = (__ENV[flag] || '').toLowerCase() === 'true';
  if (!enabled) return false;
  if (!allowMutations) {
    throw new Error(`${name} requires ALLOW_MUTATIONS=true.`);
  }
  if (values.some((value) => value === null || value === undefined || value === '')) {
    throw new Error(`${name} is enabled but its required staging variables are missing.`);
  }
  return true;
}
