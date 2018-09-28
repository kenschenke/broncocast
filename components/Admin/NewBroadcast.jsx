import React from 'react';
import PropTypes from 'prop-types';
import { mapNewBroadcastProps, mapNewBroadcastDispatch } from '../maps/Admin/NewBroadcast.map';
import { connect } from 'react-redux';
import { DuxOkDialog } from 'duxpanel';
import { NewBroadcastMessage } from './NewBroadcastMessage.jsx';
import { NewBroadcastRecipients } from './NewBroadcastRecipients.jsx';
import { NewBroadcastSchedule } from './NewBroadcastSchedule.jsx';
import { NewBroadcastAttachment } from './NewBroadcastAttachment.jsx';

class NewBroadcastUi extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            tabName: 'message',
            validMsgVisible: false
        };
    }

    isBroadcastValid = () => {
        this.setState({validMsgVisible:!this.props.isBroadcastValid});
        return this.props.isBroadcastValid;
    };

    tabClicked = (event, tab) => {
        event.preventDefault();
        this.setState({tabName:tab});
    };

    render() {
        return (
            <DuxOkDialog show={true}
                         title="New Broadcast"
                         okClassName="btn btn-primary"
                         cancelClassName="btn btn-warning"
                         onOk={this.props.okClicked}
                         onCancel={this.props.cancelClicked}
                         showCancel={true}
                         shouldClose={this.isBroadcastValid}
                         width={{
                             xs: '95%',
                             md: '50%'
                         }}
            >
                <ul className="nav nav-tabs">
                    <li className="nav-item">
                        <a href="#" onClick={e => this.tabClicked(e,'message')} className={'nav-link' + (this.state.tabName==='message' ? ' active' : '')}>Message</a>
                    </li>
                    <li className="nav-item">
                        <a href="#" onClick={e => this.tabClicked(e,'recipients')} className={'nav-link' + (this.state.tabName==='recipients' ? ' active' : '')}>Recipients</a>
                    </li>
                    <li className="nav-item">
                        <a href="#" onClick={e => this.tabClicked(e,'schedule')} className={'nav-link' + (this.state.tabName==='schedule' ? ' active' : '')}>Schedule</a>
                    </li>
                    <li className="nav-item">
                        <a href="#" onClick={e => this.tabClicked(e,'attachment')} className={'nav-link' + (this.state.tabName==='attachment' ? ' active' : '')}>Attachment</a>
                    </li>
                </ul>

                <div className={'newbroadcast-tab' + (this.state.tabName==='message' ? '' : ' d-none')}>
                    <NewBroadcastMessage/>
                </div>
                <div className={'newbroadcast-tab' + (this.state.tabName==='recipients' ? '' : ' d-none')}>
                    <NewBroadcastRecipients/>
                </div>
                <div className={'newbroadcast-tab' + (this.state.tabName==='schedule' ? '' : ' d-none')}>
                    <NewBroadcastSchedule/>
                </div>
                <div className={'newbroadcast-tab' + (this.state.tabName==='attachment' ? '' : ' d-none')}>
                    <NewBroadcastAttachment/>
                </div>

                <small className={'text-danger' + (this.state.validMsgVisible ? '' : ' invisible')} dangerouslySetInnerHTML={{__html:this.props.validMsg}}></small>
            </DuxOkDialog>
        );
    }
}

NewBroadcastUi.propTypes = {
    isBroadcastValid: PropTypes.bool.isRequired,
    validMsg: PropTypes.string.isRequired,

    cancelClicked: PropTypes.func.isRequired,
    okClicked: PropTypes.func.isRequired
};

export const NewBroadcast = connect(mapNewBroadcastProps, mapNewBroadcastDispatch)(NewBroadcastUi);
