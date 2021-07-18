const FileUploader = {
    methods: {
        /**
         * Makes a POST request to upload files asynchronously
         *
         * @param formData FormData object containing the file(s) to upload
         * @param url The URL to upload to
         * @param onUploadProgress Optional callback that is called each time upload progress changes
         *
         * @returns {Promise<AxiosResponse<any>>}
         */
        uploadFile(formData, url, onUploadProgress) {
            return axios.post(url, formData, {
                headers: {
                    "Content-Type": "multipart/form-data"
                },
                onUploadProgress
            })
        }
    }
}

export default FileUploader;
