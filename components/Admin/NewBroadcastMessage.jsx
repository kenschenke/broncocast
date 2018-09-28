import React from 'react';
import PropTypes from 'prop-types';
import { mapNewBroadcastMessageProps, mapNewBroadcastMessageDispatch } from '../maps/Admin/NewBroadcastMessage.map';
import { connect } from 'react-redux';
import { DuxForm, DuxInput } from 'duxform';

const NewBroadcastMessageUi = props => {
    return (
        <DuxForm name="broadcastmessage">
            <div className="form-group">
                <label>Short Message</label>
                <div className="input-group">
                    <DuxInput name="shortmsg"
                              className={'form-control' + (props.charsLeft < 0 ? ' is-invalid' : '')}
                    />
                    <div className="input-group-append">
                        <span className="input-group-text">{props.charsLeft} left</span>
                    </div>
                </div>
                <small style={{height:'1em'}} className={'text-danger' + (props.charsLeft < 0 ? '' : ' invisible')}>
                    The short message has too many characters
                </small>
            </div>
            <div className="form-group">
                <label>Long Message</label>
                <DuxInput className="form-control" type="textarea" rows={5} name="longmsg" maxLength={2048}/>
            </div>
        </DuxForm>
    );
};

NewBroadcastMessageUi.propTypes = {
    charsLeft: PropTypes.number.isRequired
};

export const NewBroadcastMessage = connect(mapNewBroadcastMessageProps, mapNewBroadcastMessageDispatch)(NewBroadcastMessageUi);
