/** File sizes the way people read them: bytes below 1 kB, then kB and MB with one decimal. */
export function formatBytes(bytes: number, locale: string): string {
    const number = new Intl.NumberFormat(locale, { maximumFractionDigits: 1 });
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${number.format(bytes / 1024)} kB`;
    return `${number.format(bytes / (1024 * 1024))} MB`;
}
