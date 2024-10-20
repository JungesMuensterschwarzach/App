import { Expose } from "class-transformer";

export class Error {

    @Expose()
    private type: string;

    @Expose()
    private message: string;


    public constructor(type: string, message: string) {
        this.type = type;
        this.message = message;
    }

    public getType(): string {
        return this.type;
    }

    public getMessage(): string {
        return this.message;
    }

}