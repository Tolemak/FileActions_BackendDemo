export abstract class SubApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    abstract setupEventListeners(): void;
}