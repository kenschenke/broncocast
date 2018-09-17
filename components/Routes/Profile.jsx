import React from 'react';
import PropTypes from 'prop-types';
import { mapProfileProps, mapProfileDispatch } from '../maps/Routes/Profile.map';
import { connect } from 'react-redux';
import { ProfileName } from '../Profile/ProfileName.jsx';
import { ProfileContacts } from '../Profile/ProfileContacts.jsx';
import { ProfilePassword } from '../Profile/ProfilePassword.jsx';
import { ProfileOrgs } from '../Profile/ProfileOrgs.jsx';

class ProfileUi extends React.Component {
    constructor(props) {
        super(props);
    }

    componentDidMount() {
        this.props.init();
    }

    render() {
        return (
            <div>
                <div className="row">
                    <div className="col-sm mt-2"><ProfileName/></div>
                    <div className="col-sm mt-2"><ProfileContacts/></div>
                </div>
                <div className="row">
                    <div className="col-sm mt-2"><ProfilePassword/></div>
                    <div className="col-sm mt-2"><ProfileOrgs/></div>
                </div>
            </div>
        );
    }
}

ProfileUi.propTypes = {
    init: PropTypes.func.isRequired
};

const Container = connect(mapProfileProps, mapProfileDispatch)(ProfileUi);

export default Container;
