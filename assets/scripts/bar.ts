import './tolemak-bar/tolemak-bar.js';
import { formatBytes } from './format.js';

type FieldKey = 'file' | 'size' | 'dims' | 'result';

const bar = document.querySelector<HTMLElement>('tolemak-bar');

function setField(key: FieldKey, value: string, tone?: 'accent'): void {
    if (!bar) return;
    let field = bar.querySelector<HTMLElement>(`tolemak-field[data-key="${key}"]`);
    if (!field) {
        field = document.createElement('tolemak-field');
        field.dataset.key = key;
        field.setAttribute('label', bar.dataset[`label${key[0].toUpperCase()}${key.slice(1)}`] ?? key);
        bar.append(field);
    }
    if (tone) field.setAttribute('tone', tone);
    field.textContent = value;
}

function removeField(key: FieldKey): void {
    bar?.querySelector(`tolemak-field[data-key="${key}"]`)?.remove();
}

const locale = () => document.documentElement.lang || 'en';

/** Shows the picked file in the status bar, dimensions included once the browser has decoded it. */
export function showFile(file: Blob & { readonly name: string }): void {
    removeField('result');
    setField('file', file.name);
    setField('size', formatBytes(file.size, locale()));
    createImageBitmap(file)
        .then((bitmap) => {
            setField('dims', `${bitmap.width} × ${bitmap.height}`);
            bitmap.close();
        })
        .catch(() => removeField('dims'));
}

export function clearFile(): void {
    removeField('file');
    removeField('size');
    removeField('dims');
}

export function showResult(blob: Blob): void {
    setField('result', formatBytes(blob.size, locale()), 'accent');
}

// The locale is kept by the server, so switching language is a reload with ?_locale=.
document.addEventListener('tolemak-lang', (event) => {
    const url = new URL(window.location.href);
    url.searchParams.set('_locale', (event as CustomEvent<{ lang: string }>).detail.lang);
    window.location.assign(url);
});
