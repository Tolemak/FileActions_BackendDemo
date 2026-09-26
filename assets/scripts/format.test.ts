import { describe, expect, it } from 'vitest';
import { formatBytes } from './format';

describe('formatBytes', () => {
    it('keeps small files in bytes', () => {
        expect(formatBytes(512, 'en')).toBe('512 B');
    });

    it('switches to kB and MB with one decimal', () => {
        expect(formatBytes(389_120, 'en')).toBe('380 kB');
        expect(formatBytes(2_516_582, 'en')).toBe('2.4 MB');
    });

    it('uses the page language for the decimal separator', () => {
        expect(formatBytes(2_516_582, 'pl')).toBe('2,4 MB');
    });
});
