import { describe, expect, it } from 'vitest';
import { actionUrl, optionKind, rangePrompt } from './action-options';

describe('optionKind', () => {
    it('reads the declared option type', () => {
        expect(optionKind({ option: 'range' })).toBe('range');
        expect(optionKind({ option: 'choice' })).toBe('choice');
    });

    it('treats a missing or unknown option as none', () => {
        expect(optionKind({})).toBe('none');
        expect(optionKind({ option: 'other' })).toBe('none');
    });
});

describe('actionUrl', () => {
    it('appends the option value to the action path', () => {
        expect(actionUrl('resize', 50)).toBe('/file/resize/50');
        expect(actionUrl('convert', 'png')).toBe('/file/convert/png');
    });

    it('omits the value for actions without an option', () => {
        expect(actionUrl('grayscale')).toBe('/file/grayscale');
    });
});

describe('rangePrompt', () => {
    it('builds the slider from the data attributes', () => {
        const prompt = rangePrompt({ promptTitle: 'Angle', min: '0', max: '360', step: '5', default: '90' }, 'Cancel');

        expect(prompt).toMatchObject({
            title: 'Angle',
            input: 'range',
            inputAttributes: { min: '0', max: '360', step: '5' },
            inputValue: 90,
            cancelButtonText: 'Cancel',
        });
    });
});
