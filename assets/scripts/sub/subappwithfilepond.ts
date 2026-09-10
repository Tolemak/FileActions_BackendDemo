import * as FilePond from 'filepond';
import { SubApp } from "./subapp";

export type ProcessArgs = Parameters<FilePond.ProcessServerConfigFunction>;

export abstract class SubAppWithFilePond extends SubApp {
    protected readonly _filePondElement: HTMLInputElement;
    protected readonly _filePond: FilePond.FilePond;

    constructor(filePondElement: HTMLInputElement) {
        super();
        this._filePondElement = filePondElement;
        this._filePond = this.createFilePond();
        this.setupEventListeners();
    }

    setupEventListeners(): void {
        document.querySelector(".button-process")?.addEventListener("click", () => {
            this._filePond.processFiles();
        });
    }

    private createFilePond(): FilePond.FilePond {
        return FilePond.create(
            this._filePondElement,
            {
                labelIdle: this._filePondElement.dataset.dropLabel
                    ?? `Drag & Drop your picture or <span class="filepond--label-action">Browse</span>`,
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

    abstract processFile(...args: ProcessArgs): Promise<void>;
}
