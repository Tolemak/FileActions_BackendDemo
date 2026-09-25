import 'filepond/dist/filepond.min.css';
import "../app.js";
import '../../styles/resize.css';
import Swal from 'sweetalert2';
import { getTranslations, processFileAction, saveBlobAsFile } from '../utils.js';
import { SubAppWithFilePond, type ProcessArgs } from './subappwithfilepond.js';


class SepiaApp extends SubAppWithFilePond {
    constructor() {
        super(document.querySelector('input.filepond') as HTMLInputElement);
    }

    async sweetChooseIntensity() {
        const { cancel } = getTranslations();
        return await Swal.fire({
            title: this._filePondElement.dataset.promptTitle,
            icon: "question",
            input: "range",
            inputAttributes: {
                min: "1",
                max: "100",
                step: "1"
            },
            inputValue: 80,
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
        const ret = await this.sweetChooseIntensity();
        if (ret.isDismissed) {
            abort();
            const { cancelledTitle, cancelledText } = getTranslations();
            Swal.fire(cancelledTitle, cancelledText, "error");
            return;
        }

        const result = await processFileAction(`/file/sepia/${ret.value}`, fieldName, file, load, error);
        this._filePond.removeFiles();
        if (result) {
            saveBlobAsFile(result);
        }
    }

}

new SepiaApp();
