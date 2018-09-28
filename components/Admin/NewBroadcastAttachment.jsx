import React from 'react';
import PropTypes from 'prop-types';
import { mapNewBroadcastAttachmentProps, mapNewBroadcastAttachmentDispatch } from '../maps/Admin/NewBroadcastAttachment.map';
import { connect } from 'react-redux';

class NewBroadcastAttachmentUi extends React.Component {
    constructor(props) {
        super(props);

        this._fileElem = null;
        this.state = {
            attachmentFile: ''
        };
    }

    componentDidMount() {
        this.props.init();
    }

    fileChanged = () => {
        const friendlyName = document.forms.attachment.uploadfile.files[0].name;
        this.setState({attachmentFile: friendlyName});
        this.props.uploadClicked(friendlyName);
    };

    render() {
        return (
            <form name="attachment">
                <div className="form-group">
                    <label>File To Attach</label>
                    <div className="input-group">
                        <input readOnly={true} className="form-control" value={this.state.attachmentFile}/>
                        <div className="input-group-append">
                            <button type="button" className="btn btn-secondary" onClick={() => this._fileElem.click()}>Browse</button>
                        </div>
                    </div>
                    <input ref={e => this._fileElem=e}
                           name="uploadfile"
                           type="file"
                           style={{display:'none'}}
                           onChange={this.fileChanged}
                    />
                </div>
                {this.props.uploading &&
                    <div>
                        <h5 className="text-muted">Uploading Attachment</h5>
                        <div className="progress">
                            <div className="progress-bar progress-bar-striped progress-bar-animated"
                                 aria-valuenow={100} aria-valuemin={0} aria-valuemax={100} style={{width:'100%'}}
                            >

                            </div>
                        </div>
                    </div>
                }
                {this.props.success && <h5 className="text-muted">Attachment Uploaded Successfully</h5>}
            </form>
        );
    }
}

NewBroadcastAttachmentUi.propTypes = {
    success: PropTypes.bool.isRequired,
    uploading: PropTypes.bool.isRequired,

    init: PropTypes.func.isRequired,
    uploadClicked: PropTypes.func.isRequired
};

export const NewBroadcastAttachment = connect(mapNewBroadcastAttachmentProps, mapNewBroadcastAttachmentDispatch)(NewBroadcastAttachmentUi);
