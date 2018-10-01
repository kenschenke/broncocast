import React from 'react';
import PropTypes from 'prop-types';
import {mapRegisterProps, mapRegisterDispatch} from '../maps/Profile/Register.map';
import {connect} from 'react-redux';
import {DuxForm, DuxInput} from 'duxform';
import {ProfileContacts} from './ProfileContacts.jsx';
import { Link } from 'react-router-dom';

class RegisterUi extends React.Component {
    componentDidMount() {
        this.props.init();
    }

    render() {
        return (
            <div className="container">
                <div className="row">
                    <div className="col-md-6">
                        {this.props.step === 'name' &&
                        <div>
                            <h4>Name</h4>
                            <DuxForm name="register_name" initialFocus="username">
                                <DuxInput name="username"
                                          className="form-control"
                                          placeholder="Please enter your first and last name"
                                          maxLength={30}
                                />
                                <div className="form-check">
                                    <DuxInput name="singlemsg"
                                              type="checkbox"
                                              className="form-check-input"
                                    />
                                    <label className="form-check-label">
                                        Send long broadcasts only by email and short broadcasts only by text message.
                                        When
                                        unchecked, broadcasts are sent to every phone and email address in your profile.
                                    </label>
                                </div>
                            </DuxForm>
                            <button type="button" className="btn btn-primary mt-2" onClick={this.props.nextNameClicked}>Next
                            </button>
                        </div>
                        }
                        {this.props.step === 'contacts' &&
                        <div>
                            <ProfileContacts/>
                            <Link to="/profile" className="btn btn-primary mt-2" onClick={this.props.nextContactClicked}>Done</Link>
                        </div>
                        }
                    </div>
                </div>
            </div>
        );
    }
}

RegisterUi.propTypes = {
    step: PropTypes.string.isRequired,

    init: PropTypes.func.isRequired,
    nextNameClicked: PropTypes.func.isRequired,
    nextContactClicked: PropTypes.func.isRequired
};

export const Register = connect(mapRegisterProps, mapRegisterDispatch)(RegisterUi);
