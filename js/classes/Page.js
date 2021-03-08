export default class Page {
    PreviousPage;
    Head;
    ID;

    constructor(previous, current) {
        this.PreviousPage = previous;

        let div = $("<div></div>");


        if(this.PreviousPage) {
            this.PreviousPage.hide();
            this.ID = this.PreviousPage.ID + 1;
        } else {
            this.ID = 0;
        }

        div.attr("id", "content-" + this.ID);

        $("#contentbox").append(div);
        div.load(current);

        this.Head = $("#content-" + this.ID);
    }

    previous() {
        if(this.PreviousPage) {
            Head.remove();
            this.PreviousPage.show();
            return this.PreviousPage;
        } else return this;
    };

    show() {
        this.Head.show();
    }

    hide() {
        this.Head.hide();
    }
}