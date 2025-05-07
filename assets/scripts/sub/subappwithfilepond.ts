import * as FilePond from 'filepond';
import { SubApp } from "./subapp";

export abstract class SubAppWithFilePond extends SubApp {
    protected _filePondElement: HTMLInputElement;
    protected _filePond: FilePond.FilePond;

    constructor(filePondElement: HTMLInputElement) {
        super();
        this._filePondElement = filePondElement;
        this.initFilePond();
    }
    setupEventListeners() {
        document.querySelector(".button-process")?.addEventListener("click", () => {
            this._filePond.processFiles();
        });

    }

    initFilePond() {
        this._filePond = FilePond.create(
            this._filePondElement,
            {
                labelIdle: `Drag & Drop your picture or <span class="filepond--label-action">Browse</span>`,
                stylePanelLayout: 'compact circle',
                styleLoadIndicatorPosition: 'center bottom',
                styleProgressIndicatorPosition: 'right bottom',
                styleButtonRemoveItemPosition: 'left bottom',
                styleButtonProcessItemPosition: 'right bottom',
                instantUpload: false,
                server: {
                    process: this.processFile.bind(this),
                }
            }
        );
    }

    abstract processFile(fieldName: string, file: File, _metadata, _load, _error, _progress, abort: () => void, _transfer, _options): Promise<void>;
}