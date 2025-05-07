import Swal from "sweetalert2";

export function saveBlobAsFile(blob, file) {
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = file.name;
    document.body.appendChild(a);
    a.click();
    a.remove();
}

export function sweetAlertErrorRequest(res: Response) {
    if (res.status === 400) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Bad Request",
        });
    } else if (res.status === 500) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Internal Server Error",
        });
    } else {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Unknown Error",
        });
    }
}