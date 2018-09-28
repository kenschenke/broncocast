import C from '../../../constants';
import { uploadAttachment } from '../../../actions/admin_newbroadcast';

export const mapNewBroadcastAttachmentProps = state => {
    return {
        success: state.admin_newbroadcast.attachmentSuccess,
        uploading: state.admin_newbroadcast.uploadingAttachment
    };
};

export const mapNewBroadcastAttachmentDispatch = dispatch => {
    return {
        init() {
            dispatch({
                type: C.SET_ADMIN_NEWBROADCAST_DATA,
                payload: {
                    uploadingAttachment: false,
                    attachmentSuccess: false,
                    attachmentFriendlyName: '',
                    attachmentLocalName: '',
                    attachmentMimeType: ''
                }
            });
        },

        uploadClicked(friendlyName) {
            dispatch(uploadAttachment(friendlyName));
        }
    };
};
