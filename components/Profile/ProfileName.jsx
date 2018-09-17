import React from 'react';
import PropTypes from 'prop-types';
import { mapProfileNameProps, mapProfileNameDispatch } from '../maps/Profile/ProfileName.map';
import { connect } from 'react-redux';
import { DuxForm, DuxInput } from 'duxform';

class ProfileNameUi extends React.Component {
    constructor(props) {
        super(props);
    }

    componentDidMount() {
        this.props.init();
    }

    render() {
        return (
            <div>
                <h4>Name</h4>
                <DuxForm name="profile_name">
                    <div className="input-group">
                        <DuxInput name="name" className="form-control"/>
                        <div className="input-group-append">
                            <button className="btn btn-secondary" type="button" onClick={this.props.updateClicked}>Update</button>
                        </div>
                    </div>
                    <div className="form-check">
                        <DuxInput name="singlemsg" type="checkbox" className="form-check-input" onClick={e => this.props.singleMsgClicked(e.target.checked)}/>
                        <label className="form-check-label">
                            Send long broadcasts only by email and short broadcasts only by text message. When
                            unchecked, broadcasts are sent to every phone and email address in your profile.
                        </label>
                    </div>
                </DuxForm>
            </div>
        );
    }
}

ProfileNameUi.propTypes = {
    init: PropTypes.func.isRequired,
    updateClicked: PropTypes.func.isRequired,
    singleMsgClicked: PropTypes.func.isRequired
};

export const ProfileName = connect(mapProfileNameProps, mapProfileNameDispatch)(ProfileNameUi);
