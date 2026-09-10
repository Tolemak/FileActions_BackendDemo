import 'filepond/dist/filepond.min.css';
import "../app.js";
import '../../styles/resize.css';
import Swal from 'sweetalert2';
import { getTranslations, processFileAction, saveBlobAsFile } from '../utils.js';
import { SubAppWithFilePond, type ProcessArgs } from './subappwithfilepond.js';


class ResizeApp extends SubAppWithFilePond {
    constructor() {
        super(document.querySelector('input.filepond') as HTMLInputElement);
    }

    async sweetChooseSize() {
        const { cancel } = getTranslations();
        return await Swal.fire({
            title: this._filePondElement.dataset.promptTitle,
            icon: "question",
            input: "range",
            inputAttributes: {
                min: "10",
                max: "300",
                step: "5"
            },
            inputValue: 100,
            showCancelButton: true,
            cancelButtonText: cancel,
        });
    }

    async processFile(
        fieldName: ProcessArgs[0],
        file: ProcessArgs[1],
        _metadata: ProcessArgs[2],
        load: ProcessArgs[3],
        error: ProcessArgs[4],
        _progress: ProcessArgs[5],
        abort: ProcessArgs[6],
    ): Promise<void> {
        const ret = await this.sweetChooseSize();
        if (ret.isDismissed) {
            abort();
            const { cancelledTitle, cancelledText } = getTranslations();
            Swal.fire(cancelledTitle, cancelledText, "error");
            return;
        }

        const blob = await processFileAction(`/file/resize/${ret.value}`, fieldName, file, load, error);
        this._filePond.removeFiles();
        if (blob) {
            saveBlobAsFile(blob, file);
        }
    }

}

new ResizeApp();
