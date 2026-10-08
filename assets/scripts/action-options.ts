import type { SweetAlertOptions } from 'sweetalert2';

export type OptionKind = 'range' | 'choice' | 'none';

export function optionKind(dataset: DOMStringMap): OptionKind {
    if (dataset.option === 'range' || dataset.option === 'choice') {
        return dataset.option;
    }

    return 'none';
}

export function actionUrl(action: string, value?: string | number): string {
    return value === undefined ? `/file/${action}` : `/file/${action}/${value}`;
}

export function rangePrompt(dataset: DOMStringMap, cancelButtonText: string): SweetAlertOptions {
    return {
        title: dataset.promptTitle,
        icon: 'question',
        input: 'range',
        inputAttributes: {
            min: dataset.min ?? '0',
            max: dataset.max ?? '100',
            step: dataset.step ?? '1',
        },
        inputValue: Number(dataset.default ?? 0),
        showCancelButton: true,
        cancelButtonText,
    };
}
