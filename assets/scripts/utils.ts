import Swal from 'sweetalert2';

export function saveBlobAsFile(blob: Blob, file: File): void {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = file.name;
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(url);
}

export async function sweetAlertErrorRequest(res: Response): Promise<void> {
    const text = await res.text();
    Swal.fire({
        icon: 'error',
        title: `Error ${res.status}`,
        text: text || res.statusText,
    });
}

export interface Translations {
    cancel: string;
    cancelledTitle: string;
    cancelledText: string;
    networkErrorTitle: string;
    networkErrorText: string;
}

export function getTranslations(): Translations {
    const dataset = document.body.dataset;

    return {
        cancel: dataset.i18nCancel ?? 'Cancel',
        cancelledTitle: dataset.i18nCancelledTitle ?? 'Cancelled',
        cancelledText: dataset.i18nCancelledText ?? 'Cancelled',
        networkErrorTitle: dataset.i18nNetworkErrorTitle ?? 'Network Error',
        networkErrorText: dataset.i18nNetworkErrorText ?? 'Could not reach the server. Please check your connection and try again.',
    };
}

/**
 * Uploads a file to a processing endpoint and reports the outcome back to
 * FilePond's `load`/`error` callbacks, so an item never gets stuck spinning
 * (e.g. on a dropped connection) and shows FilePond's built-in error state.
 */
export async function processFileAction(
    url: string,
    fieldName: string,
    file: File,
    load: (uniqueFileId: string) => void,
    error: (errorText: string) => void,
): Promise<Blob | null> {
    const formData = new FormData();
    formData.append(fieldName, file, file.name);

    let response: Response;
    try {
        response = await fetch(new Request(url, { method: 'POST', body: formData }));
    } catch {
        const { networkErrorTitle, networkErrorText } = getTranslations();
        error(networkErrorTitle);
        Swal.fire({ icon: 'error', title: networkErrorTitle, text: networkErrorText });
        return null;
    }

    if (response.status !== 200) {
        error(`HTTP ${response.status}`);
        await sweetAlertErrorRequest(response);
        return null;
    }

    const blob = await response.blob();
    load(String(response.status));

    return blob;
}
