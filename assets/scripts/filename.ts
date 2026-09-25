function decode(value: string): string | null {
    try {
        return decodeURIComponent(value);
    } catch {
        return null;
    }
}

function stripDirectories(name: string): string {
    return name.replace(/^.*[\\/]/, '').trim();
}

export function filenameFromContentDisposition(header: string | null, fallback: string): string {
    if (!header) {
        return fallback;
    }

    const extended = /filename\*\s*=\s*utf-8''([^;]+)/i.exec(header);
    const decoded = extended ? decode(extended[1].trim()) : null;
    if (decoded) {
        return stripDirectories(decoded) || fallback;
    }

    const plain = /filename\s*=\s*(?:"((?:\\.|[^"\\])*)"|([^;]+))/i.exec(header);
    if (plain) {
        const value = plain[1] !== undefined ? plain[1].replace(/\\(.)/g, '$1') : plain[2];
        return stripDirectories(value) || fallback;
    }

    return fallback;
}
