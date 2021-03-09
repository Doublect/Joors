export default class Page {
    PreviousPage;
    Head;
    Link;
    ID;

    constructor(previous, current) {
        this.PreviousPage = previous;

        if(this.PreviousPage) {
            this.PreviousPage.hide();
            this.ID = this.PreviousPage.ID + 1;
        } else {
            this.ID = 0;
        }

        this.Link = current;
    }


    onLoad() {
    }

    previous() {
        if(this.PreviousPage) {
            this.Head.remove();
            this.PreviousPage.show();
            return this.PreviousPage;
        } else return this;
    }

    remove() {
        if(this.PreviousPage) {
            this.Head.remove();
            return this.PreviousPage;
        } else return this;
    }

    show() {
        this.Head.show();
    }

    hide() {
        this.Head.hide();
    }
}

export function loadPage(page) {
    let div = $("<div></div>");

    div.attr("id", "content-" + page.ID);

    $("#contentbox").append(div);

    div.load(page.Link, function () {
        page.onLoad();
    });

    page.Head = $("#content-" + page.ID);
}