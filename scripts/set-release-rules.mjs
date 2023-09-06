// @ts-check

const PRODUCTION_BRANCH_NAME = 'main'
const DEVELOPMENT_BRANCH_NAME = 'develop'
// @ts-ignore
const currentBranch = process.env.GITHUB_REF_NAME
  .replace('refs/heads/', '')
  .replace('refs/pull/', '')
  .replace('/merge', '');
const isProductionRelease = currentBranch === PRODUCTION_BRANCH_NAME
const PRERELEASE_PREFIX = 'pre-'
const GROUP_DIVIDER = ','
const SEMVER_TYPES = {
  PATCH: 'patch',
  MINOR: 'minor',
  MAJOR: 'major',
}

// TODO: remove this is tagging is correctly setup without this
// if (!isProductionRelease) {
//   for (const [key, value] of Object.entries(SEMVER_TYPES)) {
//     SEMVER_TYPES[key] = `${PRERELEASE_PREFIX}${value}`
//   }
// }

console.log(`Setting rules for branch "${currentBranch}", which is ${isProductionRelease ? 'full' : 'preview'} release`);

/**
 * @type {Record<string, {semverType: typeof SEMVER_TYPES[keyof typeof SEMVER_TYPES], title: string}>}
 */
const groups = {
  fix: {
    semverType: SEMVER_TYPES.PATCH,
    title: 'Bug Fixes'
  },
  hotfix: {
    semverType: SEMVER_TYPES.PATCH,
    title: 'Hotfixes'
  },
  feature: {
    semverType: SEMVER_TYPES.MINOR,
    title: 'New Features'
  },
  chore: {
    semverType: SEMVER_TYPES.PATCH,
    title: 'Chores'
  },
  breaking: {
    semverType: SEMVER_TYPES.MAJOR,
    title: 'New Breaking Changes'
  },
  ci: {
    semverType: SEMVER_TYPES.PATCH,
    title: 'Pipeline changes'
  },
  deps: {
    semverType: SEMVER_TYPES.PATCH,
    title: 'Dependency updates'
  }
}

const result = [];
for (const [key, value] of Object.entries(groups)) {
  result.push( `${key}:${value.semverType}:${value.title}`);
}

// @ts-ignore - we don't have package.json yet to set dependencies
process.env.INPUT_CUSTOM_RELEASE_RULES = result.join(GROUP_DIVIDER);
process.env.INPUT_PRE_RELEASE_BRANCHES = DEVELOPMENT_BRANCH_NAME;
process.env.INPUT_RELEASE_BRANCHES = PRODUCTION_BRANCH_NAME;
process.env.INPUT_TYPES = Object.keys(groups).join(',');

console.log({
  releaseRules: process.env.INPUT_CUSTOM_RELEASE_RULES,
  preReleaseBranch: process.env.INPUT_PRE_RELEASE_BRANCHES,
  releaseBranch: process.env.INPUT_RELEASE_BRANCHES,
  inputTypes: process.env.INPUT_TYPES
});