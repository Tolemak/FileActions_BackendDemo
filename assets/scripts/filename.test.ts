import { describe, expect, it } from 'vitest';
import { filenameFromContentDisposition } from './filename.js';

describe('filenameFromContentDisposition', () => {
    it('reads a plain filename', () => {
        expect(filenameFromContentDisposition('attachment; filename=sample.jpg', 'sample.png')).toBe('sample.jpg');
    });

    it('prefers the UTF-8 filename over the ASCII fallback', () => {
        const header = `attachment; filename="zdjecie lodz.gif"; filename*=utf-8''zdj%C4%99cie%20%C5%82%C3%B3d%C5%BA.gif`;

        expect(filenameFromContentDisposition(header, 'x.png')).toBe('zdjęcie łódź.gif');
    });

    it('unescapes a quoted filename', () => {
        expect(filenameFromContentDisposition('attachment; filename="a \\"b\\".png"', 'x.png')).toBe('a "b".png');
    });

    it('falls back to the plain filename when the UTF-8 one is malformed', () => {
        expect(filenameFromContentDisposition(`attachment; filename=ok.png; filename*=utf-8''%E0%A4%A`, 'x.png')).toBe('ok.png');
    });

    it('drops directory parts', () => {
        expect(filenameFromContentDisposition(`attachment; filename*=utf-8''..%2F..%2Fevil.png`, 'x.png')).toBe('evil.png');
        expect(filenameFromContentDisposition('attachment; filename="..\\\\evil.png"', 'x.png')).toBe('evil.png');
    });

    it('uses the fallback when there is nothing usable', () => {
        expect(filenameFromContentDisposition(null, 'photo.png')).toBe('photo.png');
        expect(filenameFromContentDisposition('attachment', 'photo.png')).toBe('photo.png');
        expect(filenameFromContentDisposition('attachment; filename=""', 'photo.png')).toBe('photo.png');
    });
});
