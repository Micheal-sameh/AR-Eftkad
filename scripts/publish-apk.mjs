// Publishes the built Android APK for in-app updates (OTA).
//
//   1. Bump versionCode / versionName in android/app/build.gradle
//   2. cd android && gradlew assembleDebug   (or assembleRelease)
//   3. node scripts/publish-apk.mjs [--notes "what changed"] [--force] [--apk path/to/app.apk]
//   4. Deploy: the app downloads public/app/eftkad.apk and checks public/app/version.json
//
// An update only installs over the existing app if both APKs are signed with the same key,
// so always build releases on the same machine / with the same keystore.
import { createHash } from 'node:crypto';
import { copyFileSync, existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const args = process.argv.slice(2);
const flag = (name) => args.includes(name);
const value = (name) => (args.includes(name) ? args[args.indexOf(name) + 1] : undefined);

const gradle = readFileSync(join(root, 'android/app/build.gradle'), 'utf8');
const versionCode = Number(/versionCode\s+(\d+)/.exec(gradle)?.[1]);
const versionName = /versionName\s+"([^"]+)"/.exec(gradle)?.[1];
if (!versionCode || !versionName) throw new Error('Could not read versionCode/versionName from android/app/build.gradle');

const candidates = [
    value('--apk'),
    join(root, 'android/app/build/outputs/apk/release/app-release.apk'),
    join(root, 'android/app/build/outputs/apk/debug/app-debug.apk'),
].filter(Boolean);
const source = candidates.find((p) => existsSync(p));
if (!source) throw new Error('No APK found. Build it first (cd android && gradlew assembleDebug).');

const outDir = join(root, 'public/app');
mkdirSync(outDir, { recursive: true });
copyFileSync(source, join(outDir, 'eftkad.apk'));

const info = {
    versionCode,
    versionName,
    url: 'https://eftkad.avarewase.com/app/eftkad.apk',
    sha256: createHash('sha256').update(readFileSync(source)).digest('hex'),
    notes: value('--notes') ?? '',
    force: flag('--force'),
};
writeFileSync(join(outDir, 'version.json'), JSON.stringify(info, null, 2) + '\n');

console.log(`Published ${source}\n  -> public/app/eftkad.apk\n  -> public/app/version.json`, info);
