import 'filepond/dist/filepond.min.css';
import "../app.js";
import '../../styles/resize.css';
import Swal from 'sweetalert2';
import { getTranslations, processFileAction, saveBlobAsFile } from '../utils.js';
import { SubAppWithFilePond } from './subappwithfilepond.js';


class RotateApp extends SubAppWithFilePond {
    constructor() {
        super(document.querySelector('input.filepond') as HTMLInputElement);
    }

    async sweetChooseDegrees() {
        const { cancel } = getTranslations();
        return await Swal.fire({
            title: this._filePondElement.dataset.promptTitle,
            icon: "question",
            input: "range",
            inputAttributes: {
                min: "0",
                max: "360",
                step: "5"
            },
            inputValue: 90,
            showCancelButton: true,
            cancelButtonText: cancel,
        });
    }

    async processFile(fieldName: string, file: File, _metadata, load, error, _progress, abort, _transfer, _options): Promise<void> {
        const ret = await this.sweetChooseDegrees();
        if (ret.isDismissed) {
            abort();
            const { cancelledTitle, cancelledText } = getTranslations();
            Swal.fire(cancelledTitle, cancelledText, "error");
            return;
        }

        const blob = await processFileAction(`/file/rotate/${ret.value}`, fieldName, file, load, error);
        this._filePond.removeFiles();
        if (blob) {
            saveBlobAsFile(blob, file);
        }
    }

}

new RotateApp();
