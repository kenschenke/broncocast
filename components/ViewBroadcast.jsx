import React from 'react';
import PropTypes from 'prop-types';
import { DuxDialog, DuxOkDialog } from 'duxpanel';

export class ViewBroadcast extends React.Component {
    constructor(props) {
        super(props);

        this.state = {showRecipientsDialog: false};
    }

    render() {
        let buttons = [];

        if (this.props.attachmentUrl.length) {
            buttons.push(<a href={this.props.attachmentUrl} target="_blank" className="btn btn-primary">View Attachment</a>);
        }
        buttons.push({
            label: 'Close',
            className: 'btn btn-secondary',
            onClick: this.props.closeClicked
        });

        if (this.props.recipients.length) {
            buttons.push({
                label: 'Show Recipients',
                align: 'left',
                className: 'btn btn-primary',
                onClick: () => this.setState({showRecipientsDialog:true})
            });
        }
        const recipients = this.props.recipients.map(recip => <div>{recip}</div>);

        return (
            <div>
                <DuxDialog show={this.props.show}
                           title="View Broadcast"
                           allowClose={true}
                           onClose={this.props.closeClicked}
                           buttons={buttons}
                           width={{
                               sm: '95%',
                               md: '85%',
                               lg: '65%'
                           }}
                >
                    <div className="container">
                        <div className="row">
                            <div style={{fontWeight:'bold'}} className="col-sm-3 col-md-2">Sent</div>
                            <div className="col-sm-10">{this.props.delivered}</div>
                        </div>
                        <div className="row mt-2">
                            <div style={{fontWeight:'bold'}} className="col-sm-3 col-md-2">Sent By</div>
                            <div className="col-sm-10">{this.props.sentBy}</div>
                        </div>
                        <div className="row mt-2">
                            <div style={{fontWeight:'bold'}} className="col-sm-3 col-md-2">Short Msg</div>
                            <div className="col-sm-10">{this.props.shortMsg}</div>
                        </div>
                        <div className="row mt-2">
                            <div style={{fontWeight:'bold'}} className="col-sm-3 col-md-2">Long Msg</div>
                            <div className="col-sm-10">{this.props.longMsg}</div>
                        </div>
                    </div>
                </DuxDialog>
                <DuxOkDialog show={this.state.showRecipientsDialog}
                             title="Broadcast Recipients"
                             okClassName="btn btn-secondary"
                             onOk={() => this.setState({showRecipientsDialog:false})}
                             onCancel={() => this.setState({showRecipientsDialog:false})}
                             width={{
                                 sm: '95%',
                                 md: '65%',
                                 lg: '40%'
                             }}
                >
                    <div style={{height:'10em', overflowY:'auto'}}>
                        {recipients}
                    </div>
                </DuxOkDialog>
            </div>
        );
    }
}

ViewBroadcast.propTypes = {
    show: PropTypes.bool.isRequired,
    sentBy: PropTypes.string.isRequired,
    delivered: PropTypes.string.isRequired,
    shortMsg: PropTypes.string.isRequired,
    longMsg: PropTypes.string.isRequired,
    attachmentUrl: PropTypes.string.isRequired,
    recipients: PropTypes.arrayOf(PropTypes.string).isRequired,

    closeClicked: PropTypes.func.isRequired
};

ViewBroadcast.defaultProps = {
    recipients: []
};
