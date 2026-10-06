import 'filepond/dist/filepond.min.css';
import '../app.js';
import '../../styles/resize.css';
import Swal from 'sweetalert2';
import { actionUrl, optionKind, rangePrompt } from '../action-options.js';
import { getTranslations, processFileAction, saveBlobAsFile } from '../utils.js';
import { SubAppWithFilePond, type ProcessArgs } from './subappwithfilepond.js';

class ActionApp extends SubAppWithFilePond {
    constructor() {
        super(document.querySelector('input.filepond') as HTMLInputElement);
    }

    private async chooseValue(): Promise<string | number | undefined | null> {
        const dataset = this._filePondElement.dataset;
        const kind = optionKind(dataset);

        if (kind === 'choice') {
            return (document.querySelector('#action-option') as HTMLSelectElement).value;
        }
        if (kind === 'range') {
            const { cancel } = getTranslations();
            const answer = await Swal.fire(rangePrompt(dataset, cancel));

            return answer.isDismissed ? null : Number(answer.value);
        }

        return undefined;
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
        const value = await this.chooseValue();
        if (value === null) {
            abort();
            const { cancelledTitle, cancelledText } = getTranslations();
            Swal.fire(cancelledTitle, cancelledText, 'error');
            return;
        }

        const url = actionUrl(this._filePondElement.dataset.action ?? '', value);
        const result = await processFileAction(url, fieldName, file, load, error);
        this._filePond.removeFiles();
        if (result) {
            saveBlobAsFile(result);
        }
    }
}

new ActionApp();
